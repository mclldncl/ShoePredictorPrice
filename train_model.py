import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.linear_model import LinearRegression
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import OneHotEncoder
import joblib

# Load your cleaned data
df = pd.read_csv("Cleaned_Shoe_Price_Data.csv")

# Define features and target
features = ['Brand', 'Type', 'Gender', 'Size', 'Material']
X = df[features]
y = df['Price (USD)']

# Define categorical and numeric columns
categorical_features = ['Brand', 'Type', 'Gender', 'Material']
numeric_features = ['Size']

# Preprocessing
preprocessor = ColumnTransformer(
    transformers=[
        ('cat', OneHotEncoder(handle_unknown='ignore'), categorical_features)
    ],
    remainder='passthrough'  # Keep numeric as is
)

# Create model pipeline
model = Pipeline(steps=[
    ('preprocessor', preprocessor),
    ('regressor', LinearRegression())
])

# Train
model.fit(X, y)

# Save
joblib.dump(model, "shoe_price_model.pkl")
print("✅ Model saved successfully as shoe_price_model.pkl")
