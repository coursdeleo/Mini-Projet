#!/usr/bin/env python3
import json
import logging
import requests
import paho.mqtt.client as mqtt
import urllib3

# Désactive les alertes si votre serveur web n'est pas en HTTPS
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# --- CONFIGURATION ---
BROKER_IP = "172.17.2.189"  # IP de cette Raspberry Pi (le broker MQTT)
BROKER_PORT = 1883
TOPIC_BADGE = "casier/+/badge"  # Le + permet d'écouter TOUS les casiers

# Adresse exacte du fichier api.php que nous venons de créer sur votre serveur web
API_URL = "http://172.17.2.148/Mini_projet/api.php"

# Configuration des logs pour voir ce qu'il se passe dans la console
logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")

# Fonction appelée quand la passerelle se connecte au MQTT
def on_connect(client, userdata, flags, rc, properties=None):
    if rc == 0:
        logging.info("Passerelle connectée au broker MQTT local.")
        client.subscribe(TOPIC_BADGE)
        logging.info(f"Écoute sur le topic : {TOPIC_BADGE}")
    else:
        logging.error(f"Échec de connexion MQTT : code {rc}")

# Fonction appelée chaque fois qu'un badge est scanné (Message reçu en MQTT)
def on_message(client, userdata, msg):
    try:
        topic = msg.topic
        payload = json.loads(msg.payload.decode("utf-8"))
        logging.info(f"Scan détecté sur {topic} : {payload}")

        # On extrait le numéro du casier depuis le topic (ex: casier/1/badge -> 1)
        id_casier = topic.split("/")[1]
        uid_badge = payload.get("uid")

        if not uid_badge:
            logging.warning("Message reçu, mais aucun UID de badge trouvé.")
            return

        # 1. On interroge notre fichier api.php en envoyant l'UID et le Casier
        logging.info(f"Vérification du badge {uid_badge} pour le casier {id_casier} auprès de l'API...")
        try:
            res = requests.post(
                API_URL,
                json={"action": "verify", "id_casier": id_casier, "uid_badge": uid_badge},
                verify=False,
                timeout=3
            )
            
            autorise = False
            # Si le serveur web répond correctement
            if res.status_code == 200:
                data = res.json()
                # On lit la réponse {"access_granted": true/false}
                autorise = data.get("access_granted", False)
            elif res.status_code == 403:
                logging.warning(f"Accès refusé par l'API pour le badge {uid_badge}.")
            else:
                logging.error(f"Erreur API ({res.status_code}) : {res.text}")
                
        except Exception as e:
            logging.error(f"Impossible de joindre le serveur web : {e}")
            autorise = False

        # 2. On renvoie l'ordre à la serrure du casier via MQTT
        topic_reponse = f"casier/{id_casier}/cmd"
        reponse = {
            "status": "granted" if autorise else "denied",
            "unlock": autorise
        }
        
        client.publish(topic_reponse, json.dumps(reponse))
        logging.info(f"Ordre d'ouverture envoyé sur {topic_reponse} : {reponse}\n")

    except Exception as e:
        logging.error(f"Erreur lors du traitement du message : {e}")

# --- LANCEMENT DE LA PASSERELLE ---
client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.on_connect = on_connect
client.on_message = on_message

# On connecte le script au broker et on le fait tourner en boucle à l'infini
client.connect(BROKER_IP, BROKER_PORT, 60)
client.loop_forever()
