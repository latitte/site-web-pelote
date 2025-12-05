import gspread
from oauth2client.service_account import ServiceAccountCredentials
from openpyxl import Workbook

# Définir les scopes et autoriser le client
scope = ['https://spreadsheets.google.com/feeds', 'https://www.googleapis.com/auth/drive']
credentials = ServiceAccountCredentials.from_json_keyfile_name('../assets/testsheets-426617-178f6da9c492.json', scope)
client = gspread.authorize(credentials)

# Ouvrir la feuille Google et obtenir la première feuille
wsheet = client.open('Agenda').sheet1

# Récupérer toutes les données de la feuille
data = wsheet.get_all_records()

# Créer un nouveau fichier Excel
workbook = Workbook()
sheet = workbook.active
sheet.title = 'Agenda'

# Vérifier s'il y a des données
if data:
    # Écrire les en-têtes
    headers = list(data[0].keys())
    sheet.append(headers)
    
    # Écrire les lignes de données
    for row in data:
        sheet.append(list(row.values()))

# Définir le nom du fichier xlsx de sortie
output_file = 'agenda_data.xlsx'

# Enregistrer le fichier Excel
workbook.save(output_file)

print(f"Les données ont été téléchargées et sauvegardées dans le fichier {output_file}")
