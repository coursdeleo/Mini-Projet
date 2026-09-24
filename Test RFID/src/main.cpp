#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>

#define RST_PIN 22
#define SS_PIN  21

MFRC522 mfrc522(SS_PIN, RST_PIN);

void setup() {
    Serial.begin(115200);
    while (!Serial);

    SPI.begin();
    mfrc522.PCD_Init();
}

void loop() {
    // Vérifie si un badge est présent
    if (!mfrc522.PICC_IsNewCardPresent()) {
        return;
    }

    // Lit le numéro de série du badge
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

    // Affichage de l'UID brut
    Serial.print(F("UID détecté : "));
    Serial.println(currentUID);

    // Fin de la lecture pour ce badge
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();

    if (currentUID == "2A:8E:BF:24" or currentUID == "F9:13:93:C2" or currentUID == "99:9F:62:C2") {
        Serial.println(F("Badge autorisé !"));
    } else {
        Serial.println(F("Badge non autorisé !"));
    }
}