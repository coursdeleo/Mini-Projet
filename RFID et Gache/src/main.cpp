#include <SPI.h>
#include <MFRC522.h>
#include <Arduino.h>

// Broches du lecteur RFID RC522 (Liaison SPI)
#define RST_PIN 22
#define SS_PIN  21

// Broche de commande de la gâche (Relais - Borne D)
#define RELAY_PIN 26

// Broches optionnelles pour les témoins LED d'état
#define LED_GREEN 12 // Accès autorisé
#define LED_RED 14   // Accès refusé
#define LED_BLUE 27  // En attente / disponible

// Broche optionnelle pour le Buzzer
#define BUZZER_PIN 13

MFRC522 mfrc522(SS_PIN, RST_PIN);

void setLedState(bool green, bool red, bool blue) {
  digitalWrite(LED_GREEN, green ? HIGH : LOW);
  digitalWrite(LED_RED, red ? HIGH : LOW);
  digitalWrite(LED_BLUE, blue ? HIGH : LOW);
}

void setup() {
  Serial.begin(115200);
  while (!Serial);

  // Initialisation du bus SPI et du lecteur RFID
  SPI.begin();
  mfrc522.PCD_Init();

  // Configuration de la broche du relais
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW); // Relais désactivé par défaut (gâche fermée)

  // Configuration des sorties LED et Buzzer
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_RED, OUTPUT);
  pinMode(LED_BLUE, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  // État initial : LED Bleue activée (Casier disponible / en attente)
  setLedState(false, false, true);

  Serial.println("=========================================");
  Serial.println("Test Matériel ESP32 : RFID + Relais");
  Serial.println("Présentez votre badge devant le lecteur...");
  Serial.println("=========================================");
}

void loop() {
  // Détection d'une carte ou d'un badge RFID
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  // Lecture du numéro de série du badge
  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  // Construction de la chaîne UID au format XX:XX:XX:XX
  String currentUID = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) currentUID += "0";
    currentUID += String(mfrc522.uid.uidByte[i], HEX);
    if (i < mfrc522.uid.size - 1) {
      currentUID += ":";
    }
  }
  currentUID.toUpperCase();

  Serial.print(F("Badge détecté - UID : "));
  Serial.println(currentUID);

  // Vérification de l'autorisation parmi les différents badges
  if (currentUID == "2A:8E:BF:24" || currentUID == "F9:13:93:C2" || currentUID == "99:9F:62:C2") {
    Serial.println(F("--> ACCÈS AUTORISÉ : Déverrouillage de la gâche !"));
    setLedState(true, false, false); // LED Verte
    tone(BUZZER_PIN, 1000, 200);     // Bip court d'accès accepté
    digitalWrite(RELAY_PIN, HIGH);   // Déclenchement du relais (ouvre la gâche)
    delay(3000);                     // Ouverture maintenue 3 secondes
    digitalWrite(RELAY_PIN, LOW);    // Re-verrouillage automatique
    Serial.println(F("--> Gâche re-verrouillée.\n"));
  } else {
    Serial.println(F("--> ACCÈS REFUSÉ : Badge inconnu.\n"));
    setLedState(false, true, false); // LED Rouge
    // Deux bips d'accès refusé
    tone(BUZZER_PIN, 400, 250);
    delay(300);
    tone(BUZZER_PIN, 400, 250);
    delay(1000);
  }

  // Réinitialisation : Retour au statut de veille (LED Bleue)
  setLedState(false, false, true);

  // Arrêt de la communication avec la carte actuelle
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}