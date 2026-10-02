#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// -------------------------------------------------------------------
// CONFIGURATION RÉSEAU
// -------------------------------------------------------------------
const char* ssid     = "SNIR";
const char* password = "";
const char* apiUrl   = "http://172.17.2.189";
const String idCasier = "Casier-01";

// -------------------------------------------------------------------
// CONFIGURATION GPIO
// -------------------------------------------------------------------
#define SS_PIN     5
#define RST_PIN    22
#define RELAY_PIN  26
#define LED_GREEN  12
#define LED_RED    14
#define LED_BLUE   27
#define BUZZER_PIN 13

MFRC522 rfid(SS_PIN, RST_PIN);

void setLedState(bool green, bool red, bool blue) {
  digitalWrite(LED_GREEN, green ? HIGH : LOW);
  digitalWrite(LED_RED,   red   ? HIGH : LOW);
  digitalWrite(LED_BLUE,  blue  ? HIGH : LOW);
}

void connectToWiFi() {
  Serial.print("Connexion au réseau Wi-Fi : ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n--> Wi-Fi connecté avec succès !");
  Serial.print("Adresse IP de l'ESP32 : ");
  Serial.println(WiFi.localIP());
}

void setup() {
  Serial.begin(115200);

  SPI.begin();
  rfid.PCD_Init();

  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);

  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_RED, OUTPUT);
  pinMode(LED_BLUE, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  connectToWiFi();
  setLedState(false, false, true);

  Serial.println("=========================================");
  Serial.println("ESP32 Prêt - Présentez un badge RFID...");
  Serial.println("=========================================");
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();
  }

  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  String uidString = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uidString += "0";
    uidString += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) uidString += ":";
  }
  uidString.toUpperCase();

  Serial.print("Badge détecté - UID : ");
  Serial.println(uidString);

  HTTPClient http;
  http.begin(apiUrl);
  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<200> docOut;
  docOut["uid_badge"] = uidString;
  docOut["id_casier"]  = idCasier;

  String jsonPayload;
  serializeJson(docOut, jsonPayload);

  Serial.println("Envoi de la requête HTTP POST à l'API...");
  int httpResponseCode = http.POST(jsonPayload);

  bool accesAutorise = false;

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Code réponse HTTP : ");
    Serial.println(httpResponseCode);
    Serial.print("Réponse serveur : ");
    Serial.println(response);

    StaticJsonDocument<200> docIn;
    DeserializationError error = deserializeJson(docIn, response);

    if (!error) {
      accesAutorise = docIn["autorise"] | false;
    }
  } else {
    Serial.print("Erreur de communication HTTP : ");
    Serial.println(httpResponseCode);
  }

  http.end();

  if (accesAutorise) {
    Serial.println("--> ACCÈS AUTORISÉ par le serveur !");
    setLedState(true, false, false);
    tone(BUZZER_PIN, 1000, 200);

    digitalWrite(RELAY_PIN, HIGH);
    delay(3000);
    digitalWrite(RELAY_PIN, LOW);
  } else {
    Serial.println("--> ACCÈS REFUSÉ par le serveur.");
    setLedState(false, true, false);

    tone(BUZZER_PIN, 400, 250);
    delay(300);
    tone(BUZZER_PIN, 400, 250);
    delay(1000);
  }

  setLedState(false, false, true);

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}
