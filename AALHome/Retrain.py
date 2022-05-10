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
import websocket
from getmac import get_mac_address as gma

import EmotionDeepFace

def parameters():

	"""
	<model>_v<run>_<mode>_<epochs>_<batches>.h5'

	model: # Model to build
	-m DeepFace
	-m VGG
	-m VGGFace
	-m FaceNet
	-m OpenFace

	run: # Number of the test/Version of model
	-r 1
	-r 2
	-r 3
	...
	-r N

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
<model>_v<run>_<mode>_<epochs>_<batches>.h5'

model: # Model to build
-m DeepFace
-m VGG
-m VGGFace
-m FaceNet
-m OpenFace

run: # Number of the test/Version of model
-r 1
-r 2
-r 3
...
-r N

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

	if len(sys.argv) == 16:
		print("Missing parameters, run -h or --help")
		exit()

	for i, arg in enumerate(sys.argv):
		if i == 0:
			continue
		elif arg == '-a':
			activation = str(sys.argv[i+1]) 	
		elif arg == '-l':
			loss = str(sys.argv[i+1])
		elif arg == '--metrics':
			metrics = str(sys.argv[i+1])
		elif arg == '-e' or arg == '-b' or arg == '-r':
			try:
				number = int(sys.argv[i+1])
				
				if number <= 0:
					print("Numbers must be positive")
					exit()

				if arg == '-e': 
					epochs = number
					
				elif arg == '-b':
					batch = number

				elif arg == '-r':
					run = number
				
			except ValueError as e:
				print("Parameters are numeric")
				exit()
		elif arg == '-m':
			model = str(sys.argv[i+1])
		else: 
			continue
	
	return model, run, epochs, batch, activation, loss, metrics


def build_model(model_name, dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics):

	"""
	This function builds a deepface model
	Parameters:
		model_name (string): face recognition or facial attribute model
			VGG-Face, Facenet, OpenFace, DeepFace, DeepID for face recognition
			Age, Gender, Emotion, Race for facial attributes
	Returns:
		built deepface model
	"""

	global model_obj #singleton design pattern

	models = {
		'EmotionDeepFace': EmotionDeepFace.loadModel,
	}

	if not "model_obj" in globals():
		model_obj = {}

	if not model_name in model_obj.keys():
		model = models.get(model_name)
		if model:
			model,class_indices = model(
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
			#print(model_name," built")
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
run = params[1]
epochs = params[2]
batches = params[3]
activationFunction = params[4]
lossFunction = params[5]
metrics = params[6]

if metrics == 'binary_accuracy':
    mode = 'binary'
else:
    mode = 'categorical'

modelPath = 'weights/'+str(model)+'_v'+str(run)+'_'+mode+'_'+str(epochs)+'_'+str(batches)+'.h5'
classIndicesPath = 'analysis/class_indices.json'
            
start_time_retrain = time.time()

# Websocket Log
API_URL = os.getenv('API_URL')

f = open("token.txt", "r")
token = f.read()

r = requests.get(url = API_URL+'/auth/user', headers={"Authorization": "Bearer "+token})
userId = r.json()["id"]

MAC_ADDRESS = gma()

websocket.enableTrace(True)
ws = websocket.WebSocket()
ws.connect(os.getenv('LOG_WEBSOCKET_URL')+str(userId))
try:
	while True:
		# Check if each folder in pre-dataset has RETRAIN_NUMBER_FILES frames each, if it has the frames are moved to the dataset    
		while ( int(start_time_retrain + RETRAIN_TIME) <= time.time() ):
			if os.path.exists(PRE_DATASET_PATH) == True:           
				emotionsPath = [w.replace(os.sep, '/') for w in  glob(PRE_DATASET_PATH+'/*')]		
				for emotionPath in emotionsPath:
					files = [w.replace(os.sep, '/') for w in  glob(emotionPath+'/*.jpg')]		
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
							destination = DATASET_PATH + '/' + pathComplement + '/' + emotion + '/' +file.split('/')[-1]
							shutil.move(file, destination)

						# Retrain of the model
						# TODO - Make a copy of previous model for security reasons
						modelRetrain = build_model('EmotionDeepFace', DATASET_PATH, modelPath,classIndicesPath,True, epochs, batches, activationFunction, lossFunction, metrics)
			start_time_retrain = time.time()
except Exception as e:
	ws.send(MAC_ADDRESS+";"+sys.argv[0]+";"+"Cliente Ligado"+";"+CLIENT_EMAIL)
