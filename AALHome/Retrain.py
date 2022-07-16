from asyncio import subprocess
import warnings

warnings.filterwarnings("ignore")
import sys
import os
import time
from glob import glob
from dotenv import load_dotenv

load_dotenv()
import random
import shutil
import requests
from os.path import exists
from getmac import get_mac_address as gma
import socketio

import EmotionDeepFace

default_model = {
    'model': "DeepFace",
    'epochs': 500,
    'batches': 128,
    'activation': "sigmoid",
    'loss': "binary_crossentropy",
    'metrics': "binary_accuracy"
}


def parameters():
    """
	<model>_<mode>_<epochs>_<batches>.h5'

	model: # Model to build
	-m DeepFace
	-m VGG
	-m VGGFace
	-m FaceNet
	-m OpenFace

	epochs: # How many epochs
	-e 1
	-e 2
	-e 3
	...
	-e N

	batches: # Number of inputs for epoch
	-b 1
	-b 2
	-b 3
	...
	-b N

	activation: # Activation Function
	-a sigmoid
	-a sofmax
	...

	loss: # Loss Function
	-l categorical_crossentropy
	-l binary_crossentropy
	...

	metrics: # Metrics
	--metrics binary_accuracy
	--metrics accuracy


	"""

    if '-h' in sys.argv or '--help' in sys.argv:
        print("""
<model>_<mode>_<epochs>_<batches>.h5'

model: # Model to build
-m DeepFace
-m VGG
-m VGGFace
-m FaceNet
-m OpenFace

epochs: # How many epochs
-e 1
-e 2
-e 3
...
-e N

batches: # Number of inputs for epoch
-b 1
-b 2
-b 3
...
-b N

activation: # Activation Function
-a sigmoid
-a sofmax
...

loss: # Loss Function
-l categorical_crossentropy
-l binary_crossentropy
...

metrics: # Metrics
--metrics binary_accuracy
--metrics accuracy

		""")
        exit()

    model = default_model.get('model')
    epochs = default_model.get('epochs')
    batch = default_model.get('batches')
    activation = default_model.get('activation')
    loss = default_model.get('loss')
    metrics = default_model.get('metrics')

    for i, arg in enumerate(sys.argv):
        if i == 0:
            continue
        elif arg == '-a':
            activation = str(sys.argv[i + 1])
        elif arg == '-l':
            loss = str(sys.argv[i + 1])
        elif arg == '--metrics':
            metrics = str(sys.argv[i + 1])
        elif arg == '-e' or arg == '-b':
            try:
                number = int(sys.argv[i + 1])

                if number <= 0:
                    print("Numbers must be positive")
                    exit()

                if arg == '-e':
                    epochs = number

                elif arg == '-b':
                    batch = number

            except ValueError as e:
                print("Parameters are numeric")
                exit()
        elif arg == '-m':
            model = str(sys.argv[i + 1])
        else:
            continue

    return model, epochs, batch, activation, loss, metrics


def build_model(model_name, dataset_dir, modelPath, classIndicesPath, forceRetrain, epochs, batches, activation, loss,
                metrics):
    """
	This function builds a deepface model
	Parameters:
		model_name (string): face recognition or facial attribute model
			VGG-Face, Facenet, OpenFace, DeepFace, DeepID for face recognition
			Age, Gender, Emotion, Race for facial attributes
	Returns:
		built deepface model
	"""

    global model_obj  # singleton design pattern

    models = {
        'EmotionDeepFace': EmotionDeepFace.loadModel,
    }

    if not "model_obj" in globals():
        model_obj = {}

    if not model_name in model_obj.keys():
        model = models.get(model_name)
        if model:
            model, class_indices = model(
                dataset_dir=dataset_dir,
                modelPath=modelPath,
                classIndicesPath=classIndicesPath,
                forceRetrain=True,
                epochs=epochs,
                batch_size=batches,
                activation=activation,
                loss=loss,
                metrics=metrics
            )
            model_obj[model_name] = model
        # print(model_name," built")
        else:
            raise ValueError('Invalid model_name passed - {}'.format(model_name))

    return model_obj[model_name]


RETRAIN_TIME = int(os.getenv('RETRAIN_TIME'))
PRE_DATASET_PATH = os.getenv('PRE_DATASET_PATH')
DATASET_PATH = os.getenv('DATASET_PATH')
RETRAIN_NUMBER_FILES = os.getenv('RETRAIN_NUMBER_FILES')

CLIENT_EMAIL = os.getenv('CLIENT_EMAIL')

params = parameters()
model = params[0]
epochs = params[1]
batches = params[2]
activationFunction = params[3]
lossFunction = params[4]
metrics = params[5]

if metrics == 'binary_accuracy':
    mode = 'binary'
else:
    mode = 'categorical'

modelPath = 'weights/' + str(model) + '_' + mode + '_' + str(epochs) + '_' + str(batches) + '.h5'
classIndicesPath = 'analysis/class_indices.json'

start_time_retrain = 0#time.time()

# Websocket Log
API_URL = os.getenv('API_URL')

while not exists("token.txt"):
    time.sleep(3)
    continue

f = open("token.txt", "r")
token = f.read()

r = requests.get(url=API_URL + '/auth/user', headers={"Authorization": "Bearer " + token})
userId = r.json()["data"]["id"]

MAC_ADDRESS = gma()

# standard Python
sio = socketio.Client()
sio.connect(os.getenv('WEBSOCKET_URL'))
sio.emit('logged_in', {"username": str(userId), "userType": "C"})
try:
    while True:
        # Check if each folder in pre-dataset has RETRAIN_NUMBER_FILES frames each, if it has the frames are moved to the dataset
        while start_time_retrain == 0 or (int(start_time_retrain + RETRAIN_TIME) <= time.time()):
            if os.path.exists(PRE_DATASET_PATH) == True:
                emotionsPath = [w.replace(os.sep, '/') for w in glob(PRE_DATASET_PATH + '/*')]
                for emotionPath in emotionsPath:
                    files = [w.replace(os.sep, '/') for w in glob(emotionPath + '/*.jpg')]
                    if len(files) >= int(RETRAIN_NUMBER_FILES):
                        emotion = emotionPath.split('/')[1]
                        if os.path.exists(DATASET_PATH + "/train/" + emotion) == False:
                            os.mkdir(DATASET_PATH + "/train/" + emotion)
                            os.mkdir(DATASET_PATH + "/test/" + emotion)
                        for file in files:
                            # 20% for test 80% for train
                            if random.randint(0, 100) > 20:
                                pathComplement = "train"
                            else:
                                pathComplement = "test"
                            destination = DATASET_PATH + '/' + pathComplement + '/' + emotion + '/' + file.split('/')[
                                -1]
                            shutil.move(file, destination)

                        # Retrain of the model
                        if os.path.exists('previousModels') is False:
                            os.mkdir('previousModels')
                        modelName = str(model) + '_' + mode + '_' + str(epochs) + '_' + str(batches) + '.h5'
                        destination = 'previousModels/' + modelName
                        shutil.copy(modelPath, destination)
                        modelRetrain = build_model('EmotionDeepFace', DATASET_PATH, modelPath, classIndicesPath, True,
                                                   epochs, batches, activationFunction, lossFunction, metrics)
            start_time_retrain = time.time()
except Exception as e:
    sio.emit('newLogMessage', {"userId": str(userId), "data": MAC_ADDRESS + ";" + sys.argv[0] + ";" + "Error: " + str(e) + ";" + CLIENT_EMAIL})
