#include <Arduino.h>
#include <SPI.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <MFRC522.h>

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET    -1
#define SCREEN_ADDRESS 0x3C

#define OLED_SDA_PIN 21
#define OLED_SCL_PIN 22

#define SS_PIN  5
#define RST_PIN 4
#define SCK_PIN 18
#define MISO_PIN 19
#define MOSI_PIN 23

#define RELAY_PIN 26

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Liste des badges autorisés
const String badgesAutorises[] = {
"2A:8E:BF:24",
"F9:13:93:C2",
"99:9F:62:C2"
};
const int nombreBadges = 3;

void afficherOLED(String ligne1, String ligne2, String ligne3 = "") {
display.clearDisplay();
display.setTextSize(1);
display.setTextColor(SSD1306_WHITE);

display.setCursor(0, 0);
display.println(F("=== SYSTEME RFID ==="));

display.setCursor(0, 16);
display.println(ligne1);

display.setCursor(0, 32);
display.println(ligne2);

if (ligne3 != "") {
display.setCursor(0, 48);
display.println(ligne3);
}

display.display();
}

void setup() {
Serial.begin(115200);

Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);

// Initialisation de l'écran OLED
if(!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
  Serial.println(F("Erreur d'allocation SSD1306 OLED !"));
  for(;;);
}

display.clearDisplay();
afficherOLED("Initialisation...", "Veuillez patienter");
delay(1500);

// Initialisation SPI et RFID avec les broches physiques de l'ESP32
SPI.begin(SCK_PIN, MISO_PIN, MOSI_PIN, SS_PIN);
mfrc522.PCD_Init();

// Configuration du relais de la gâche
pinMode(RELAY_PIN, OUTPUT);
digitalWrite(RELAY_PIN, LOW);

afficherOLED("Statut : PRET", "Passez votre badge...");

Serial.println(F("="));
Serial.println(F("Système ESP32 : RFID + OLED + Relais"));
Serial.println(F("Prêt à scanner..."));
Serial.println(F("="));
}

void loop() {
// Détection d'un badge
if (!mfrc522.PICC_IsNewCardPresent()) {
return;
}

if (!mfrc522.PICC_ReadCardSerial()) {
return;
}

// Lecture de l'UID
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

// Vérification de l'accès
bool accesAutorise = false;
for (int i = 0; i < nombreBadges; i++) {
if (currentUID == badgesAutorises[i]) {
accesAutorise = true;
break;
}
}

if (accesAutorise) {
Serial.println(F("--> ACCÈS AUTORISÉ : Déverrouillage de la gâche !"));
afficherOLED("ACCES AUTORISE", "UID:", currentUID);
digitalWrite(RELAY_PIN, HIGH); // Déclenchement du relais

delay(3000); // Maintien de l'ouverture (3 secondes)

digitalWrite(RELAY_PIN, LOW);  // Re-verrouillage
Serial.println(F("--> Gâche re-verrouillée.\n"));


} else {
Serial.println(F("--> ACCÈS REFUSÉ : Badge inconnu.\n"));
afficherOLED("ACCES REFUSE", "Inconnu:", currentUID);
delay(1500);
}

// Retour à l'état de veille
afficherOLED("Statut : PRET", "Passez votre badge...");

mfrc522.PICC_HaltA();
mfrc522.PCD_StopCrypto1();
}