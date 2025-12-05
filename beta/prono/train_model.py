import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import mean_absolute_error, r2_score
import joblib

# Charger les données depuis le fichier CSV
df = pd.read_csv('data.csv')

# Encoder les équipes
le = LabelEncoder()
# Ajuster le label encoder sur toutes les équipes uniques
le.fit(pd.concat([df['equipe1'], df['equipe2']]))

# Transformer les colonnes equipe1 et equipe2
df['equipe1'] = le.transform(df['equipe1'])
df['equipe2'] = le.transform(df['equipe2'])

# Préparer les caractéristiques (features) et les étiquettes (labels)
X = df[['equipe1', 'equipe2']]
y = df[['score_equipe1', 'score_equipe2']]

# Diviser les données en ensembles d'entraînement et de test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Créer et entraîner le modèle
model = LinearRegression()
model.fit(X_train, y_train)

# Prédire les scores pour l'ensemble de test
y_pred = model.predict(X_test)

# Calculer les erreurs et le coefficient de détermination
mae = mean_absolute_error(y_test, y_pred)
r2 = r2_score(y_test, y_pred)

print(f"Erreur Absolue Moyenne (MAE): {mae:.2f}")
print(f"Coefficient de Détermination (R²): {r2:.2f}")

# Sauvegarder le modèle et le label encoder
joblib.dump(model, 'model.joblib')
joblib.dump(le, 'label_encoder.joblib')

print("Modèle entraîné et sauvegardé avec succès.")
