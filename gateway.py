#!/usr/bin/env python3
import json
import logging
import requests
import paho.mqtt.client as mqtt
import urllib3

# Ignore l'avertissement lié au certificat HTTPS autosigné du sujet de TP
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

BROKER_IP = "172.17.2.189"
BROKER_PORT = 1883
TOPIC_BADGE = "casier/+/badge"

# Adresse IP réelle du serveur web / API
API_URL = "http://172.17.2.148/Mini_projet/api.php"

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")

def on_connect(client, userdata, flags, rc, properties=None):
    if rc == 0:
        logging.info("Passerelle connectée au broker MQTT local.")
        client.subscribe(TOPIC_BADGE)
        logging.info(f"Écoute sur le topic : {TOPIC_BADGE}")
    else:
        logging.error(f"Échec de connexion MQTT : code {rc}")

def on_message(client, userdata, msg):
    try:
        topic = msg.topic
        payload = json.loads(msg.payload.decode("utf-8"))
        logging.info(f"Données reçues sur {topic} : {payload}")

        id_casier = topic.split("/")[1]
        uid_badge = payload.get("uid")

        if not uid_badge:
            logging.warning("Message sans UID de badge reçu.")
            return

        # Appel API vers le serveur central
        logging.info(f"Vérification du badge {uid_badge} auprès de l'API...")
        try:
            res = requests.post(
                API_URL,
                json={"action": "verify", "id_casier": id_casier, "uid_badge": uid_badge},
                verify=False,
                timeout=3
            )
            autorise = False
            if res.status_code == 200:
                data = res.json()
                autorise = data.get("access_granted", False)
            else:
                logging.error(f"Erreur API ({res.status_code}) : {res.text}")
        except Exception as e:
            logging.error(f"Impossible de joindre le serveur API : {e}")
            autorise = False

        # Renvoi de l'ordre d'ouverture à l'ESP32
        topic_reponse = f"casier/{id_casier}/cmd"
        reponse = {
            "status": "granted" if autorise else "denied",
            "unlock": autorise
        }
        client.publish(topic_reponse, json.dumps(reponse))
        logging.info(f"Ordre envoyé sur {topic_reponse} : {reponse}")

    except Exception as e:
        logging.error(f"Erreur traitement : {e}")

client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.on_connect = on_connect
client.on_message = on_message

client.connect(BROKER_IP, BROKER_PORT, 60)
client.loop_forever()
