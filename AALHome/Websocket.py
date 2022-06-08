#import websocket
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
sio = socketio.Client(logger=True, engineio_logger=True)

@sio.on("newFrameMessage")
async def message(data):
    print("something!!!!!")
    if (len(data) > 70):
        print("New Frame Classified!")
        data = json.loads(data)
        messageContent = json.loads(data["image"])

        if "," in messageContent["image"]:
            messageContent = messageContent.split(",")[1]

        image64 = bytes(messageContent, 'ascii')

        # If path does not exists, we make it

        if os.path.exists(PRE_DATASET_PATH) == False:
            os.mkdir(PRE_DATASET_PATH)

        path = PRE_DATASET_PATH + "/" + data["emotion"]

        if os.path.exists(path) == False:
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

@sio.on('*')
def catch_all(event, data):
    print(data)
    pass

if __name__ == "__main__":
    # standard Python
    while not exists("token.txt"):
        time.sleep(3)
        continue
    f = open("token.txt", "r")
    token = f.read()

    API_URL = os.getenv('API_URL')

    r = requests.get(url=API_URL + '/auth/user', headers={"Authorization": "Bearer " + token})
    userId = r.json()["data"]["id"]

    sio.connect(os.getenv('WEBSOCKET_URL'))
    sio.emit('logged_in', {"username":str(userId), "userType":"C"})
    print("Logged In - Successful")
    sio.wait()
    rel.signal(2, rel.abort)  # Keyboard Interrupt
    rel.dispatch()
