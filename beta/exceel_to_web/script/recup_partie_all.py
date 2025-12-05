import pandas as pd
from datetime import datetime, date
import locale
import mysql.connector

# Définir la locale en français
locale.setlocale(locale.LC_TIME, 'fr_FR.UTF-8')

# Fonction pour déterminer le numéro de la semaine en juillet
def numero_semaine_juillet(date):
    debut_juillet = datetime(date.year, 7, 1)
    delta = date - debut_juillet
    return delta.days // 7 + 1

# Fonction pour récupérer les numéros de téléphone et les stocker
def get_phone_numbers_and_store(numeros_equipe, heure, date):
    config = {
        'user': 'root',
        'password': '',
        'host': 'localhost',
        'database': 'pelote_ilharre'
    }
    
    try:
        conn = mysql.connector.connect(**config)
        cursor = conn.cursor()

        if not numeros_equipe or numeros_equipe == "pas de partie":
            print(f"Partie à {heure}: pas de partie")
            return

        if isinstance(numeros_equipe, dict):
            query = "SELECT `Numéro Equipe`, Telephone FROM equipe WHERE `Numéro Equipe` IN (%s, %s)"
            numeros_equipe_list = [numeros_equipe["equipe1"], numeros_equipe["equipe2"]]

            cursor.execute(query, numeros_equipe_list)
            results = cursor.fetchall()

            insert_query = "INSERT INTO partie_de_demain (`Numéro Equipe`, Telephone, Heure, Date) VALUES (%s, %s, %s, %s)"
            for equipe_numero, telephone in results:
                cursor.execute(insert_query, (equipe_numero, telephone, heure, date))

            conn.commit()
        else:
            print(f"Partie à {heure} n'est pas un dictionnaire valide.")

    except mysql.connector.Error as err:
        print(f"Erreur: {err}")
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

# Exemple d'utilisation
date_actuelle = datetime.now()
semaine = numero_semaine_juillet(date_actuelle)

aujourdhui = date_actuelle.strftime('%A')

print(f"Semaine: {semaine}")
print(f"Aujourd'hui: {aujourdhui}")



semaine = "2"
aujourdhui = "vendredi"


# Charger le fichier Excel
file_path = 'script/agenda_data.xlsx'
excel_data = pd.ExcelFile(file_path)

# Charger la feuille "Agenda Poules" dans un DataFrame
agenda_poules_df = pd.read_excel(file_path, sheet_name='Agenda')

# Sélectionner les parties à différentes heures
partie_18h30 = agenda_poules_df.loc[agenda_poules_df['id'] == f'18h30/{semaine}', aujourdhui].values
partie_19h15 = agenda_poules_df.loc[agenda_poules_df['id'] == f'19h15/{semaine}', aujourdhui].values
partie_20h = agenda_poules_df.loc[agenda_poules_df['id'] == f'20h/{semaine}', aujourdhui].values

# print(f"Partie à 18h30: {partie_18h30}")
# print(f"Partie à 19h15: {partie_19h15}")
# print(f"Partie à 20h: {partie_20h}")

# Traiter les résultats des parties
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

# Appel de la fonction pour chaque partie avec la date spécifiée
if isinstance(partie_18h30, dict):
    get_phone_numbers_and_store(partie_18h30, "18:30", date_actuelle)


if isinstance(partie_19h15, dict):
    get_phone_numbers_and_store(partie_19h15, "19:15", date_actuelle)


if isinstance(partie_20h, dict):
    get_phone_numbers_and_store(partie_20h, "20:00", date_actuelle)

