import websocket
import _thread
import time
import rel
import os
import requests
import uuid
import base64
import json
from dotenv import load_dotenv
load_dotenv()

DATABASE_PATH = os.getenv('DATABASE_PATH')

def on_message(ws, message):
    if (len(message) > 50):

        message = json.loads(message)
        messageContent = json.loads(message["content"])

        if "," in messageContent["image"]:
            messageContent["image"] = messageContent["image"].split(",")[1]

        image64 = bytes(messageContent["image"], 'ascii')

        with open(DATABASE_PATH + "/" + messageContent["emotion"] + str(uuid.uuid4())+'.jpg', "wb") as fh:
            fh.write(base64.decodebytes(image64))
            

def on_error(ws, error):
    print("Error:" + error)

def on_close(ws, close_status_code, close_msg):
    print("### closed ###")

def on_open(ws):
    print("Opened connection")

if __name__ == "__main__":
    websocket.enableTrace(True)
    f = open("token.txt", "r")
    token = f.read()

    API_URL = os.getenv('API_URL')

    r = requests.get(url = API_URL+'/auth/user', headers={"Authorization": "Bearer "+token})
    userId = r.json()["id"]

    ws = websocket.WebSocketApp(os.getenv('WEBSOCKET_URL')+str(userId),
                              on_open=on_open,
                              on_message=on_message,
                              on_error=on_error,
                              on_close=on_close)

    ws.run_forever(dispatcher=rel)  # Set dispatcher to automatic reconnection
    rel.signal(2, rel.abort)  # Keyboard Interrupt
    rel.dispatch()