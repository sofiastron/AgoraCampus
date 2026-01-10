// app/Services/ChatGPTPlanner.php
<?php


use Illuminate\Support\Facades\Http;

class ChatGPTPlanner
{
    public function genererAvecIA($modules, $enseignants, $salles, $contraintes)
    {
        $prompt = $this->creerPrompt($modules, $enseignants, $salles, $contraintes);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert en génération d\'emploi du temps universitaire.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
        ]);
        
        return $this->parserReponse($response->json());
    }
    
    private function creerPrompt($modules, $enseignants, $salles, $contraintes)
    {
        return "
        Génére-moi un emploi du temps universitaire avec ces données :
        
        MODULES À PLANIFIER :
        " . json_encode($modules, JSON_PRETTY_PRINT) . "
        
        ENSEIGNANTS DISPONIBLES :
        " . json_encode($enseignants, JSON_PRETTY_PRINT) . "
        
        SALLES DISPONIBLES :
        " . json_encode($salles, JSON_PRETTY_PRINT) . "
        
        CONTRAINTES :
        " . json_encode($contraintes, JSON_PRETTY_PRINT) . "
        
        RÈGLES :
        1. Chaque module doit avoir son volume horaire respecté
        2. Un enseignant ne peut pas être à deux endroits en même temps
        3. Une salle ne peut pas accueillir deux cours en même temps
        4. Respecter les préférences horaires des enseignants
        5. Éviter les cours trop tôt ou trop tard
        
        Donne-moi le résultat au format JSON avec ce format :
        {
          \"lundi\": {
            \"08:00-10:00\": [
              {\"module\": \"Python\", \"enseignant\": \"Dupont\", \"salle\": \"Salle 101\", \"groupe\": \"G1\"}
            ],
            \"10:00-12:00\": [...]
          },
          \"mardi\": {...}
        }
        ";
    }
}