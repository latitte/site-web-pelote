import mysql.connector
import csv

# Connexion à la base de données MySQL
conn = mysql.connector.connect(
    host='mysql-tittdev.alwaysdata.net',
    user='tittdev',
    password='titi64120$',
    database='tittdev_ilharre'
)
cursor = conn.cursor()

# Sélectionner les données de la table calendrier
cursor.execute("SELECT id, jours, heure, partie, score, niveau FROM calendrier")
rows = cursor.fetchall()

# Transformer les données et créer un tableau
tableau_transforme = []

for row in rows:
    id, jours, heure, partie, score, niveau = row
    
    # Vérifier que partie et score ne sont pas None
    if partie and score:
        # Extraire les équipes et les scores
        try:
            equipe1, equipe2 = map(int, partie.split('/'))
            score_equipe1, score_equipe2 = map(int, score.split('/'))
            
            # Ajouter les données transformées au tableau
            tableau_transforme.append((id, jours, heure, equipe1, equipe2, score_equipe1, score_equipe2))
        except ValueError as e:
            print(f"Erreur de transformation pour la ligne avec l'id {id}: {e}")
    else:
        print(f"Les données de la ligne avec l'id {id} sont incomplètes et ne seront pas incluses.")

# Fermer la connexion
conn.close()

# Afficher le tableau transformé
print("Tableau transformé:")
print("id | jours | heure | equipe1 | equipe2 | score_equipe1 | score_equipe2 | niveau")
for ligne in tableau_transforme:
    print(" | ".join(map(str, ligne)))

# Écrire les données dans un fichier CSV
with open('data.csv', mode='w', newline='') as file:
    writer = csv.writer(file)
    # Écrire l'en-tête
    writer.writerow(["id", "jours", "heure", "equipe1", "equipe2", "score_equipe1", "score_equipe2"])
    # Écrire les données
    writer.writerows(tableau_transforme)

print("Les données transformées ont été écrites dans data.csv")
