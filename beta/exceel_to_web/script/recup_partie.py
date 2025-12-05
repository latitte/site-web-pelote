import pandas as pd
from datetime import datetime
import locale

# Définir la locale en français
locale.setlocale(locale.LC_TIME, 'fr_FR.UTF-8')

# Récupérer le jour de la semaine en toutes lettres
aujourdhui = datetime.now().strftime('%A')

def numero_semaine_juillet(date):
    # Définir le 1er juillet de l'année en cours
    debut_juillet = datetime(date.year, 7, 1)
    
    # Calculer la différence en jours
    delta = date - debut_juillet
    
    # Calculer le numéro de la semaine
    numero_semaine = delta.days // 7 + 1
    
    return numero_semaine

# Exemple d'utilisation
date_actuelle = datetime.now()
semaine = numero_semaine_juillet(date_actuelle)


semaine = "1"
aujourdhui = "lundi"


# Charger le fichier Excel
file_path = 'agenda_data.xlsx'
excel_data = pd.ExcelFile(file_path)

# Charger la feuille "Agenda Poules" dans un DataFrame
agenda_poules_df = pd.read_excel(file_path, sheet_name='Agenda')

# Sélectionner la valeur à la ligne "18h30/1" et la colonne correspondant à aujourd'hui
partie_18h30 = agenda_poules_df.loc[agenda_poules_df['id'] == f'18h30/{semaine}', aujourdhui].values
partie_19h15 = agenda_poules_df.loc[agenda_poules_df['id'] == f'19h15/{semaine}', aujourdhui].values
partie_20h = agenda_poules_df.loc[agenda_poules_df['id'] == f'20h/{semaine}', aujourdhui].values

# Vérifier si la valeur sélectionnée n'est pas vide avant de la traiter
if len(partie_18h30) > 0 and isinstance(partie_18h30[0], str):
    equipe1, equipe2 = partie_18h30[0].split('/')
    partie_18h30 = {"equipe1": equipe1, "equipe2": equipe2}
else:
    partie_18h30 = "pas de partie"

if len(partie_19h15) > 0 and isinstance(partie_19h15[0], str):
    equipe1, equipe2 = partie_19h15[0].split('/')
    partie_19h15 = {"equipe1": equipe1, "equipe2": equipe2}
else:
    partie_19h15 = "pas de partie"

if len(partie_20h) > 0 and isinstance(partie_20h[0], str):
    equipe1, equipe2 = partie_20h[0].split('/')
    partie_20h = {"equipe1": equipe1, "equipe2": equipe2}
else:
    partie_20h = "pas de partie"

# Affichage des résultats
print(f"Partie à 18h30: {partie_18h30}")
print(f"Partie à 19h15: {partie_19h15}")
print(f"Partie à 20h: {partie_20h}")
