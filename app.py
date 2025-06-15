import streamlit as st
import pandas as pd
import joblib
import mysql.connector
import os
from datetime import datetime

st.set_page_config(page_title="Shoe Price Predictor", layout="wide")

# === Load trained model ===
@st.cache_resource
def load_model():
    return joblib.load("shoe_price_model.pkl")

model = load_model()

# === Save individual prediction to MySQL ===
def insert_prediction_to_db(brand, type_, gender, material, size, predicted_price):
    if not all([brand, type_, gender, material]) or predicted_price is None or predicted_price <= 0:
        st.warning("⚠️ Prediction not saved due to missing or invalid data.")
        return

    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="shoedb"
        )
        cursor = conn.cursor()
        query = """
            INSERT INTO predictions (brand, type, gender, material, size, predicted_price, created_at)
            VALUES (%s, %s, %s, %s, %s, %s, NOW())
        """
        values = (brand, type_, gender, material, size, round(predicted_price, 2))
        cursor.execute(query, values)
        conn.commit()
        cursor.close()
        conn.close()
        st.toast("✅ Prediction saved to database!", icon="💾")
    except Exception as e:
        st.error(f"❌ Database error: {e}")

# === Custom CSS Styling ===
st.markdown("""
    <style>
        html, body { font-family: 'Segoe UI', sans-serif; }
        h1, h2 { color: #7b2cbf; margin-bottom: 0.5em; }
        .stButton>button {
            background-color: #7b2cbf; color: white;
            border-radius: 0.5em; font-size: 16px;
        }
        .stDownloadButton>button {
            background-color: #4cc9f0; color: white;
        }
    </style>
""", unsafe_allow_html=True)

# === Sidebar ===
st.sidebar.image("https://cdn-icons-png.flaticon.com/512/2331/2331970.png", width=60)
st.sidebar.header("👟 Shoe Price Predictor")
st.sidebar.markdown("Upload your shoe dataset or manually enter details to estimate prices.")

# === Title ===
st.markdown("# 👟 Shoe Price Predictor")
st.markdown("### Predict sneaker prices using machine learning")
st.divider()

# === Upload CSV File ===
st.subheader("📂 Upload Your CSV File")
uploaded_file = st.file_uploader("Upload a CSV with columns: Brand, Type, Gender, Material, Size", type=["csv"])

expected_columns = ['Brand', 'Type', 'Gender', 'Material', 'Size']
UPLOAD_FOLDER = os.path.join("php", "uploads")
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

if uploaded_file:
    try:
        # Save to local file
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"{timestamp}_{uploaded_file.name}".replace(" ", "_")
        file_path = os.path.join(UPLOAD_FOLDER, filename)
        with open(file_path, "wb") as f:
            f.write(uploaded_file.getbuffer())

        input_df = pd.read_csv(file_path, on_bad_lines='skip')

        if 'Size' in input_df.columns and input_df['Size'].dtype == object:
            input_df['Size'] = input_df['Size'].str.extract(r'([\d.]+)').astype(float)

        if not all(col in input_df.columns for col in expected_columns):
            st.error("❌ CSV missing required columns: Brand, Type, Gender, Material, Size")
        else:
            input_df = input_df[expected_columns]
            st.success("✅ File loaded successfully")
            st.dataframe(input_df, use_container_width=True)

            if st.button("🔮 Predict Prices"):
                predictions = model.predict(input_df)
                input_df["Predicted Price (USD)"] = predictions
                st.success("✅ Predictions complete!")
                st.dataframe(input_df, use_container_width=True)
                st.download_button("💾 Download Results", input_df.to_csv(index=False), "predicted_prices.csv", "text/csv")

    except Exception as e:
        st.error(f"❌ Error processing file: {e}")

st.divider()

# === Manual Prediction Entry ===
st.subheader("✍️ Enter a Shoe to Predict")

brands = ['Nike', 'Adidas', 'Puma', 'Reebok', 'Converse']
types = ['Running', 'Casual', 'Basketball', 'Fashion']
genders = ['Men', 'Women']
materials = ['Leather', 'Mesh', 'Canvas', 'Primeknit']

col1, col2 = st.columns(2)
with col1:
    brand = st.selectbox("Brand", brands)
    type_ = st.selectbox("Type", types)
    gender = st.radio("Gender", genders, horizontal=True)
with col2:
    material = st.selectbox("Material", materials)
    size = st.slider("Shoe Size", 6.0, 12.0, 9.0, 0.5)

if st.button("💰 Predict This Shoe"):
    form_df = pd.DataFrame([{
        'Brand': brand,
        'Type': type_,
        'Gender': gender,
        'Material': material,
        'Size': size
    }])
    try:
        predicted = model.predict(form_df)[0]
        st.success(f"Estimated Price: **${predicted:.2f}**")
        insert_prediction_to_db(brand, type_, gender, material, size, predicted)
    except Exception as e:
        st.error(f"Prediction error: {e}")

st.divider()

# === Footer ===
st.markdown("<center style='color: #aaa;'>Predictive Modeling of Retail Shoe Prices Using Machine Learning</center>", unsafe_allow_html=True)
