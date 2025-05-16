#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include "DHTesp.h"

int pinDHT = 15;
DHTesp dht;

int relay = 13; // relay pin
int soilMoisturePin = 34; // Soil moisture sensor pin

const char* SSID = "tum";
const char* PASSWORD = "oooooooo";
const char* LINE_TOKEN = "SyMyIczyA9X83N90l6vrLMI0Jdpl8GOgwaQAVrCGo2v";
const char* LINE_API_URL = "https://notify-api.line.me/api/notify";

int soilMoistureThreshold = 3395; // Threshold for soil moisture 

// task for core 0 (DHT and Soil Moisture sensor)
void core0Task(void * pvParameters) {
  HTTPClient http;
  while (true) {
    vTaskDelay(pdMS_TO_TICKS(300000)); // delay this task
    if (WiFi.status() == WL_CONNECTED) {
      // อ่านค่าจาก DHT
      TempAndHumidity data = dht.getTempAndHumidity();
      Serial.printf("Humidity: %.2f%%, Temperature: %.1f°C\n", data.humidity, data.temperature);
      
      // อ่านค่าความชื้นในดิน
      int soilMoistureValue = analogRead(soilMoisturePin);
      Serial.printf("Soil Moisture Value: %d\n", soilMoistureValue);
      
      // ตรวจสอบค่าความชื้นในดิน
      if (soilMoistureValue > soilMoistureThreshold) {
        Serial.println("Soil is too dry! Sending notification...");
        sendLineNotification("ความชื้นในดินต่ำเกินไป! 🌱💧 กรุณารดน้ำต้นไม้"); 
      }
      
      // ส่งค่าไปยัง API
      if (http.begin("https://tnj-development.com/hightech/backend/API/getReq.php")) {
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        int httpResponseCode = http.POST("humidity=" + String(data.humidity, 2) +
                                         "&temperature=" + String(data.temperature, 1) +
                                         "&soilMoisture=" + String(soilMoistureValue));
        if (httpResponseCode > 0) {
          Serial.printf("HTTP POST response code: %d\n", httpResponseCode);
        } else {
          Serial.printf("HTTP POST request failed, error: %s\n", http.errorToString(httpResponseCode).c_str());
        }
        http.end();
      } else {
        Serial.println("Unable to connect to server");
      }
    }
  }
}

// task for core 1 (Watering control via API)
void core1Task(void * pvParameters) {
  HTTPClient http;
  while (true) {
    vTaskDelay(pdMS_TO_TICKS(1500)); // หยุด Task นี้ไว้ 1.5 วินาที
    if (WiFi.status() == WL_CONNECTED) {
      if (http.begin("https://tnj-development.com/hightech/backend/API/watering.json")) {
        int httpResponseCode = http.GET();
        if (httpResponseCode > 0) {
          String payload = http.getString();
          StaticJsonDocument<200> doc;
          DeserializationError error = deserializeJson(doc, payload);
          if (!error) {
            int sec = doc["sec"];
            if (sec >= 1000) {
              digitalWrite(relay, HIGH); // เปิดรีเลย์
              vTaskDelay(pdMS_TO_TICKS(sec)); // รอ sec วินาที
              digitalWrite(relay, LOW); // ปิดรีเลย์
              sendLineNotification("รดน้ำต้นไม้เสร็จเรียบร้อยแล้ว💦🌲");

              HTTPClient httpClose;
              if (httpClose.begin("https://tnj-development.com/hightech/backend/API/closeWatering.api.php")) {
                httpClose.addHeader("Content-Type", "application/x-www-form-urlencoded");
                int closeResponseCode = httpClose.POST("command=watered&sec=" + String(sec));
                if (closeResponseCode > 0) {
                  Serial.printf("HTTP POST response code for closing: %d\n", closeResponseCode);
                } else {
                  Serial.printf("HTTP POST request failed for closing, error: %s\n", httpClose.errorToString(closeResponseCode).c_str());
                }
                httpClose.end();
              } else {
                Serial.println("Unable to connect to close watering server");
              }
            }
          } else {
            Serial.print("deserializeJson() failed: ");
            Serial.println(error.c_str());
          }
        } else {
          Serial.printf("HTTP GET request failed with code: %d\n", httpResponseCode);
        }
        http.end();
      } else {
        Serial.println("Unable to connect to watering.json server");
      }
    }
  }
}

void setup() {
  Serial.begin(115200);
  // relay setup
  pinMode(relay, OUTPUT);
  digitalWrite(relay, LOW);

  // wifi setup
  WiFi.begin(SSID, PASSWORD);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");

  dht.setup(pinDHT, DHTesp::DHT11);
  pinMode(soilMoisturePin, INPUT);

  xTaskCreatePinnedToCore(core0Task, "Core0Task", 8192, NULL, 1, NULL, 0);
  xTaskCreatePinnedToCore(core1Task, "Core1Task", 8192, NULL, 1, NULL, 1);
}

void loop() {
  // this loop is required
}

void sendLineNotification(String message) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(LINE_API_URL);
    http.addHeader("Authorization", "Bearer " + String(LINE_TOKEN));
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String payload = "message=" + message;
    int httpResponseCode = http.POST(payload);
    if (httpResponseCode > 0) {
      Serial.println("Notification sent successfully");
    } else {
      Serial.println("Error in sending notification");
    }
    http.end();
  } else {
    Serial.println("Error: Not connected to WiFi");
  }
}
