import joblib
import numpy as np

# Charger le modèle et le label encoder
model = joblib.load('model.joblib')
le = joblib.load('label_encoder.joblib')

# Fonction pour prédire le résultat d'un match entre deux équipes
def predire_resultat(equipe1, equipe2):
    # Encoder les équipes
    equipe1_encoded = le.transform([equipe1])[0]
    equipe2_encoded = le.transform([equipe2])[0]
    
    # Prédiction du score
    prediction = model.predict([[equipe1_encoded, equipe2_encoded]])
    
    # Scores bruts prédits
    score_equipe1 = prediction[0][0]
    score_equipe2 = prediction[0][1]
    
    # Ajustement pour respecter la règle de 40 points
    if score_equipe1 >= 40 and score_equipe2 >= 40:
        if score_equipe1 > score_equipe2:
            score_equipe1 = 40
            score_equipe2 = min(int(round(score_equipe2)), 40)
        else:
            score_equipe2 = 40
            score_equipe1 = min(int(round(score_equipe1)), 40)
    elif score_equipe1 >= 40:
        score_equipe1 = 40
        score_equipe2 = min(int(round(score_equipe2)), 40)
    elif score_equipe2 >= 40:
        score_equipe2 = 40
        score_equipe1 = min(int(round(score_equipe1)), 40)
    else:
        if score_equipe1 > score_equipe2:
            score_equipe1 = 40
            score_equipe2 = int(round(score_equipe2))
        else:
            score_equipe2 = 40
            score_equipe1 = int(round(score_equipe1))
    
    return int(score_equipe1), int(score_equipe2)

# Demander à l'utilisateur de saisir deux équipes
equipe1 = input("Entrez l'équipe 1 : ")
equipe2 = input("Entrez l'équipe 2 : ")

# Prédire le résultat
score_equipe1, score_equipe2 = predire_resultat(equipe1, equipe2)
print(f"Prédiction: {equipe1} vs {equipe2} -> Score: {score_equipe1}-{score_equipe2}")
