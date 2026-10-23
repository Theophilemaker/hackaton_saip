"""
train_yield_model.py
Trains an XGBoost yield model on NISR SAS 2025 data and exports
coefficients to models/yield_model.json for PHP to consume.
"""

import os
import json
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.metrics import r2_score, mean_absolute_error
from sklearn.ensemble import GradientBoostingRegressor

# ── Paths ──────────────────────────────────────────────
RAW = "data/nisr/raw"
OUT_DIR = "models"
os.makedirs(OUT_DIR, exist_ok=True)

DISTRICT_MAP = {
    'Bugesera':0,'Burera':1,'Gakenke':2,'Gasabo':3,'Gatsibo':4,
    'Gicumbi':5,'Gisagara':6,'Huye':7,'Kamonyi':8,'Karongi':9,
    'Kayonza':10,'Kicukiro':11,'Kirehe':12,'Muhanga':13,'Musanze':14,
    'Ngoma':15,'Ngororero':16,'Nyabihu':17,'Nyagatare':18,'Nyamagabe':19,
    'Nyamasheke':20,'Nyanza':21,'Nyarugenge':22,'Nyaruguru':23,'Rubavu':24,
    'Ruhango':25,'Rulindo':26,'Rusizi':27,'Rutsiro':28,'Rwamagana':29,
}

CROP_MAP = {
    'Maize':0,'Beans':1,'Rice':2,'Irish Potato':3,'Sweet Potato':4,
    'Cassava':5,'Banana':6,'Coffee':7,'Tea':8,'Tomato':9,'Onion':10,'Cabbage':11
}

# ── Load NISR data ─────────────────────────────────────
def load_nisr():
    candidates = [
        f"{RAW}/Rwa_raw_SeasonA2025_Production.csv",
        "data/nisr/processed/sector_yields_2025.csv",
    ]
    for path in candidates:
        if os.path.exists(path):
            print(f"✅ Loading {path}")
            df = pd.read_csv(path, low_memory=False)
            return df
    print("⚠️  No NISR CSV found — generating synthetic training data")
    return synthetic_data()

def synthetic_data():
    """Fallback: generate synthetic data matching published NISR averages."""
    np.random.seed(42)
    districts = list(DISTRICT_MAP.keys())
    crops = list(CROP_MAP.keys())
    rows = []
    for _ in range(5000):
        d = np.random.choice(districts)
        c = np.random.choice(crops)
        fert = np.random.uniform(0, 200)
        irrig = np.random.uniform(0, 60)
        ph = np.random.uniform(4.0, 7.5)
        rain = np.random.uniform(500, 1600)
        # Base yield by crop
        base = {
            'Maize':2100,'Beans':1200,'Rice':4800,'Irish Potato':12500,
            'Sweet Potato':8400,'Cassava':14200,'Banana':12000,'Coffee':800,
            'Tea':2200,'Tomato':18000,'Onion':15500,'Cabbage':22000
        }[c]
        # Effects
        fert_f = 1 + min(0.5, (fert / 50) * 0.15)
        irrig_f = 1 + min(0.4, (irrig / 10) * 0.08)
        ph_f = 0.6 if ph < 5 else 0.78 if ph < 5.5 else 0.92 if ph < 6 else 1.0
        rain_f = 0.72 if rain < 600 else 0.88 if rain < 800 else 1.0 if rain < 1200 else 0.94
        y = base * fert_f * irrig_f * ph_f * rain_f
        y += np.random.normal(0, y * 0.08)  # noise
        rows.append({
            'district': d, 'crop_name': c, 'avg_yield_kg_ha': max(200, y),
            'fertilizer_kg': fert, 'irrigation_pct': irrig,
            'soil_ph': ph, 'rainfall_mm': rain,
        })
    return pd.DataFrame(rows)

# ── Prepare features ───────────────────────────────────
def prepare(df):
    # Rename columns to canonical names
    rename = {
        'district_name':'district','crop':'crop_name',
        'fertilizer_kg_ha':'fertilizer_kg','yield_kg_ha':'avg_yield_kg_ha',
    }
    df = df.rename(columns={k:v for k,v in rename.items() if k in df.columns})

    # Ensure required columns
    for col in ['district','crop_name','avg_yield_kg_ha','fertilizer_kg',
                'irrigation_pct','soil_ph','rainfall_mm']:
        if col not in df.columns:
            if col == 'fertilizer_kg':   df[col] = 45
            elif col == 'irrigation_pct':df[col] = 12
            elif col == 'soil_ph':       df[col] = 5.8
            elif col == 'rainfall_mm':   df[col] = 900

    df = df.dropna(subset=['avg_yield_kg_ha'])
    df = df[df['avg_yield_kg_ha'] > 0]
    df = df[df['avg_yield_kg_ha'] < 50000]

    df['district_code'] = df['district'].map(DISTRICT_MAP).fillna(15).astype(int)
    df['crop_code']     = df['crop_name'].map(CROP_MAP).fillna(0).astype(int)

    X = df[['district_code','crop_code','fertilizer_kg',
            'irrigation_pct','soil_ph','rainfall_mm']].values
    y = df['avg_yield_kg_ha'].values
    return X, y

# ── Train ──────────────────────────────────────────────
def train():
    df = load_nisr()
    X, y = prepare(df)
    print(f"📊 Training on {len(X):,} samples")

    X_tr, X_te, y_tr, y_te = train_test_split(X, y, test_size=0.2, random_state=42)

    model = GradientBoostingRegressor(
        n_estimators=200, max_depth=5, learning_rate=0.05, random_state=42
    )
    model.fit(X_tr, y_tr)

    y_pred = model.predict(X_te)
    r2 = r2_score(y_te, y_pred)
    mae = mean_absolute_error(y_te, y_pred)
    print(f"🎯 R² score: {r2:.3f}")
    print(f"📏 MAE: {mae:.1f} kg/ha")

    # Export as linear approximation for PHP
    # We linearize around the mean using partial dependencies
    means = X_tr.mean(axis=0)
    coefs = {}
    feature_names = ['district_code','crop_code','fertilizer_kg_ha',
                     'irrigation_pct','soil_ph','rainfall_mm']
    delta = 0.01
    for i, name in enumerate(feature_names):
        x1 = means.copy(); x1[i] += delta
        x0 = means.copy(); x0[i] -= delta
        coefs[name] = (model.predict([x1])[0] - model.predict([x0])[0]) / (2 * delta)

    output = {
        'model_type': 'gradient_boosting_v1',
        'base_score': float(model.predict([means])[0]),
        'coefficients': {k: round(v, 4) for k, v in coefs.items()},
        'r2_score': round(r2, 3),
        'mae_kg_ha': round(mae, 1),
        'training_samples': len(X),
        'feature_means': {name: float(means[i]) for i, name in enumerate(feature_names)},
        'trained_at': pd.Timestamp.now().isoformat(),
    }

    with open(f"{OUT_DIR}/yield_model.json", 'w') as f:
        json.dump(output, f, indent=2)

    print(f"✅ Model exported to {OUT_DIR}/yield_model.json")

if __name__ == '__main__':
    train()