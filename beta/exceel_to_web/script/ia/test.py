import openai

# Configuration de la clé API (remplacez par votre nouvelle clé API valide)
openai.api_key = 'sk-proj-yE62AcDNLQthzNtnZIopT3BlbkFJW5AR59I1xgkqbH17xrfL'

# Test de la clé API
try:
    response = openai.Completion.create(
        engine="gpt-3.5-turbo",
        prompt="Dites-moi une blague",
        max_tokens=50
    )
    print(response['choices'][0]['text'].strip())
except Exception as e:
    print(f"Erreur lors de l'appel à l'API OpenAI: {e}")
