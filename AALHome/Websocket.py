# import websocket
import socketio
import rel
import os
import requests
import uuid
import base64
import json
import time
from dotenv import load_dotenv
from os.path import exists

load_dotenv()

PRE_DATASET_PATH = os.getenv('PRE_DATASET_PATH')
sio = socketio.Client()


@sio.on("newFrameMessage")
def message(data):
    if len(data["image"]) > 70:
        print("New Frame Classified!")
        messageContent = data["image"]

        if "," in messageContent:
            messageContent = messageContent.split(",")[1]

        image64 = bytes(messageContent, 'ascii')

        # If path does not exists, we make it

        if not os.path.exists(PRE_DATASET_PATH):
            os.mkdir(PRE_DATASET_PATH)

        path = PRE_DATASET_PATH + "/" + data["emotion"]

        if not os.path.exists(path):
            os.mkdir(path)

        with open(path + "/" + str(uuid.uuid4()) + '.jpg', "wb") as fh:
            fh.write(base64.decodebytes(image64))


@sio.on('connect')
def connect():
    print("connected")


@sio.event
def connect_error(data):
    print("The connection failed!")


@sio.on('disconnect')
def disconnect(sid):
    print('disconnect ', sid)


if __name__ == "__main__":
    while True:
        # standard Python
        while not exists("token.txt"):
            time.sleep(3)
            continue

        f = open("token.txt", "r")
        token = f.read()

        API_URL = os.getenv('API_URL')

        r = requests.get(url=API_URL + '/auth/user', headers={"Authorization": "Bearer " + token})

        if r.status_code != 200:
            API_URL = os.getenv('API_URL')
            API_ENDPOINT = API_URL + "/auth/login"
            CLIENT_EMAIL = os.getenv('CLIENT_EMAIL')
            CLIENT_PASSWORD = os.getenv('CLIENT_PASSWORD')

            json = {
                "email": CLIENT_EMAIL,
                "password": CLIENT_PASSWORD
            }

            r = requests.post(url=API_ENDPOINT, json=json)
            if r.status_code == 200:
                # extracting response text
                token = r.json()["access_token"]
                with open('token.txt', 'w') as f:
                    f.write(token)

            r = requests.get(url=API_URL + '/auth/user', headers={"Authorization": "Bearer " + token})

        if r.status_code == 200:
            userId = r.json()["data"]["id"]

            sio.connect(os.getenv('WEBSOCKET_URL'))
            sio.emit('logged_in', {"username": str(userId), "userType": "C"})
            print("Logged In - Successful")
            sio.wait()
            rel.signal(2, rel.abort)  # Keyboard Interrupt
            rel.dispatch()

        time.sleep(10)
