import socketio
from dotenv import load_dotenv
import os
import requests
from os.path import exists
load_dotenv()

sio = socketio.Client()


@sio.event
def newFrameMessage(data):
    print("something!!!!!")
 
@sio.event
def newLogMessage(data):
    print(".......")


@sio.on('connect')
def connect():
    print("connected")

@sio.event
def connect_error(data):
    print("The connection failed!")

@sio.on('disconnect')
def disconnect(sid):
    print('disconnect ', sid)

API_URL = os.getenv('API_URL')
API_ENDPOINT = API_URL + "/auth/login"
CLIENT_EMAIL = os.getenv('CLIENT_EMAIL')
CLIENT_PASSWORD = os.getenv('CLIENT_PASSWORD')

json = {
    "email": CLIENT_EMAIL,
    "password": CLIENT_PASSWORD
}

if __name__ == "__main__":
    # standard Python
    while not exists("token.txt"):
        time.sleep(3)
        continue
    r = requests.post(url=API_ENDPOINT, json=json)  
    if r.status_code == 200:
        # extracting response text
        token = r.json()["access_token"]
        with open('token.txt', 'w') as f:
            f.write(token)

        API_URL = os.getenv('API_URL')

        r = requests.get(url=API_URL + '/auth/user', headers={"Authorization": "Bearer " + token})
        userId = r.json()["data"]["id"]

        sio.connect(os.getenv('WEBSOCKET_URL'))
        sio.emit('logged_in', {"username":str(userId), "userType":"C"})
        print("Logged In - Successful")
        sio.wait()
    rel.signal(2, rel.abort)  # Keyboard Interrupt
    rel.dispatch()
