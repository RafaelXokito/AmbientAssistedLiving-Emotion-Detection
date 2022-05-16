from asyncio import subprocess
import warnings
warnings.filterwarnings("ignore")

import sys

import EmotionDeepFace

import os
import time
from datetime import datetime
import requests
from glob import glob

from dotenv import load_dotenv
load_dotenv()
from getmac import get_mac_address as gma

import platform

default_model = {
	'model': "DeepFace",
	'epochs': 500, 
	'batches': 128,
	'activation': "sigmoid",
	'loss': "binary_crossentropy",
	'metrics': "binary_accuracy"
}


def parameters():

	if '-h' in sys.argv or '--help' in sys.argv:
		print("""
<model>_v<run>_<mode>_<epochs>_<batches>.h5'

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

forceRetrain: # Force retraining model
-f
--force

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

time: # Time (seconds) for human labelling iteration
-t <natural number>
--time <natural number>
		""")
		exit()

	if len(sys.argv) < 3 or len(sys.argv) > 18:
		print("Missing parameters, run -h or --help")
		exit()
	
	forceRetrain = False

	model = default_model.get('model')
	epochs = default_model.get('epochs')
	batch = default_model.get('batches')
	activation = default_model.get('activation')
	loss = default_model.get('loss')
	metrics = default_model.get('metrics')
	
	for i, arg in enumerate(sys.argv):
		if i == 0:
			continue
		elif arg == '-f' or arg == '--force':
			forceRetrain = True
		elif arg == '-a':
			activation = str(sys.argv[i+1]) 	
		elif arg == '-l':
			loss = str(sys.argv[i+1])
		elif arg == '--metrics':
			metrics = str(sys.argv[i+1])
		elif arg == '-t' or arg == '--time':
			time_HLIteration = int(sys.argv[i+1])
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
				
			except ValueError as e:
				print("Parameters are numeric")
				exit()
		elif arg == '-m':
			model = str(sys.argv[i+1])
		else: 
			continue
	
	return model, epochs, batch, forceRetrain, activation, loss, metrics,time_HLIteration

def build_model(model_name, dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics):

	"""
	This function builds a deepface model
	Parameters:
		model_name (string): face recognition or facial attribute model
			DeepFace
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
				forceRetrain=forceRetrain,
				epochs=epochs, 
				batch_size=batches,
				activation=activation,
				loss=loss,
				metrics=metrics
			)
			model_obj[model_name] = model
		else:
			raise ValueError('Invalid model_name passed - {}'.format(model_name))

	return model_obj[model_name]

def processTopFrames(predictionEmotion, arrayTopDominantAccuracies, arrayTopPredictions, arrayOrderedTopPredictions, emotion, frame, previous, previousPrediction, emotionPredictions, times, arrayTopFramePaths):
	"""
	Order the top predictions array in descrescent order, of the first percentage is smaller than the 
	current frame's prediction we have to replace one with the other
	"""
	if os.path.exists('top10Frames') is False:
		os.mkdir('top10Frames')
	
	if os.path.exists('top10Frames/'+emotion) is False:
		os.mkdir('top10Frames/'+emotion)

	count = len(arrayTopFramePaths)
	now = int(time.time())
	
	if now != previous or previousPrediction < predictionEmotion:  # or previousPrediction < predictionEmotion:
		# if on different seconds
		if now != previous:
			previousPrediction = 0					
		# if on the same second
		else:
			previousPrediction = predictionEmotion
		
		if len(arrayTopDominantAccuracies) >= 10:

			filenameFrameLowerValue = ""

			if now == previous and previousPrediction < predictionEmotion:
				arrayTopDominantAccuracies.pop()
				arrayTopPredictions.pop()
				times.pop()
				arrayTopFramePaths.pop()

				filenameFrameLowerValue = arrayTopFramePaths[-1]

			elif now != previous and arrayOrderedTopPredictions[0] < predictionEmotion:

				# finds original index of lower prediction value
				lowerValueIndex = arrayTopDominantAccuracies.index(arrayOrderedTopPredictions[0])

				filenameFrameLowerValue = arrayTopFramePaths[lowerValueIndex]

				# deletes the lower value from the top 10 array and folder
				arrayTopDominantAccuracies.pop(lowerValueIndex)
				arrayTopPredictions.pop(lowerValueIndex)
				times.pop(lowerValueIndex)
				arrayTopFramePaths.pop(lowerValueIndex)

			if filenameFrameLowerValue != "":
				if os.path.exists(filenameFrameLowerValue) is True:
					os.remove(filenameFrameLowerValue)

				# adds the new frame to the top 10 array and folder
				arrayTopDominantAccuracies.append(predictionEmotion)

				aux = ""
				keys = list(emotionPredictions.keys())
				values = list(emotionPredictions.values())
				for index in range(len(emotionPredictions.keys())):
					aux += keys[index] + "#" + str(values[index]) + ";"
				aux = aux[:-1]
				arrayTopPredictions.append(aux)

				filePath = filenameFrameLowerValue

				cv2.imwrite(filePath, frame)
				previous = int(time.time())
				times.append(previous)
				arrayTopFramePaths.append(filePath)

				arrayOrderedTopPredictions = sorted(arrayTopDominantAccuracies)
		else:
			count = count + 1

			# Controi a string de previsões para gráfico de modal
			aux = ""
			keys = list(emotionPredictions.keys())
			values = list(emotionPredictions.values())
			for index in range(len(emotionPredictions.keys())):
				aux += keys[index]+"#"+str(values[index])+";"
			aux = aux[:-1]

			if now == previous and previousPrediction < predictionEmotion and len(times) != 0:
				times.pop()
				arrayTopPredictions.pop()
				arrayTopDominantAccuracies.pop()
				arrayTopFramePaths.pop()
				count = count - 1

			if now != previous or now == previous and previousPrediction < predictionEmotion:
				arrayTopDominantAccuracies.append(predictionEmotion)
				arrayTopPredictions.append(aux)

				filePath = 'top10Frames/'+emotion+'/frame_'+str(count)+'.jpg'

				cv2.imwrite(filePath, frame)
				previous = int(time.time())

				times.append(previous)
				arrayTopFramePaths.append(filePath)

				arrayOrderedTopPredictions = sorted(arrayTopDominantAccuracies)

	return arrayTopDominantAccuracies, arrayTopPredictions, arrayOrderedTopPredictions, previous, previousPrediction, times, arrayTopFramePaths


def resetFolderFrames():
	emotionsPath =  [w.replace(os.sep, '/') for w in  glob(TOP_FRAMES_PATH+'/*')]
	for emotionPath in emotionsPath:		
		if os.path.exists(emotionPath) is True:			
			files =  [w.replace(os.sep, '/') for w in  glob(emotionPath+'/*.jpg')]		
			if len(files) > 0:
				for f in files:
					os.remove(f)	

def update_date(path_to_file):
    if platform.system() == 'Windows':
        return os.path.getmtime(path_to_file)
    else:
        stat = os.stat(path_to_file)
        try:
            return stat.st_birthtime
        except AttributeError:
            # We're probably on Linux. No easy way to get creation dates here,
            # so we'll settle for when its content was last modified.
            return stat.st_mtime

#validar os parametros
params = parameters()

import cv2
from EmotionDetection import analyze
import numpy as np

import websocket

TOP_FRAMES_PATH = os.getenv('TOP_FRAMES_PATH')

API_URL = os.getenv('API_URL')
CLIENT_EMAIL = os.getenv('CLIENT_EMAIL')
CLIENT_PASSWORD = os.getenv('CLIENT_PASSWORD')

MAC_ADDRESS = gma()
# defining the api-endpoint 
API_ENDPOINT = API_URL+"/auth/login"

json = {
    "email": CLIENT_EMAIL,
    "password": CLIENT_PASSWORD
}

# sending post request and saving response as response object
r = requests.post(url = API_ENDPOINT, json=json)

if r.status_code == 200:
	# extracting response text 
	token = r.json()["token"]

	with open('token.txt', 'w') as f:
		f.write(token)

	API_URL = os.getenv('API_URL')

	r = requests.get(url = API_URL+'/auth/user', headers={"Authorization": "Bearer "+token})
	userId = r.json()["id"]

	websocket.enableTrace(True)
	ws = websocket.WebSocket()
	ws.connect(os.getenv('LOG_WEBSOCKET_URL')+str(userId))

	ws.send(MAC_ADDRESS+";"+sys.argv[0]+";"+"Cliente Ligado"+";"+CLIENT_EMAIL)

	# Run websocket client
	#p = subprocess.Popen([sys.executable, 'Websocket.py'], 
	#                                   stdout=subprocess.PIPE, 
	#                                   stderr=subprocess.STDOUT)


	video=cv2.VideoCapture(0)  #requisting the input from the webcam or camera

	model = params[0]
	epochs = params[1]
	batches = params[2]
	forceRetrain = params[3]
	activationFunction = params[4]
	lossFunction = params[5]
	metrics = params[6]
	time_HLIteration = params[7]



	datasetPath = 'FER-2013'

	if metrics == 'binary_accuracy':
		mode = 'binary'
	else:
		mode = 'categorical'

	modelPath = 'weights/'+str(model)+'_'+mode+'_'+str(epochs)+'_'+str(batches)+'.h5'
	classIndicesPath = 'analysis/class_indices.json'
	model = build_model('EmotionDeepFace', datasetPath, modelPath,classIndicesPath,forceRetrain, epochs, batches, activationFunction, lossFunction, metrics)

	# POPable vars
	framesDominantAccuraciesTop10Emotions = []
	framesPredictionsTop10Emotions = []
	times = []
	arrayTopFramePaths = []

	orderedPredictionsTop10Emotions = []

	resetFolderFrames()

	previous = []
	previousPrediction = []

	auxmodelCreationDate = update_date(modelPath)

	emotions = []

	while True:

		start_time = time.time()
		modelCreationDate = update_date(modelPath)
		if auxmodelCreationDate != modelCreationDate:
			model = build_model('EmotionDeepFace', datasetPath, modelPath,classIndicesPath,False, epochs, batches, activationFunction, lossFunction, metrics)
		
		#Irá capturar vídeo até chegar ao tempo parametrizado (segundos) e fazer passar à fase de human labelling
		while ( int(time.time() - start_time) < time_HLIteration ):  #checking if are getting video feed and using it
			_, frame = video.read()

			try:
				result = analyze(
					frame,
					model=model,
				)

				if result["dominant_emotion"] != "Not Found":
					img=cv2.rectangle(frame, (result["region"]["x"], result["region"]["y"]), (result["region"]["x"]+result["region"]["w"], result["region"]["y"]+result["region"]["h"]), (0,0,255),1)
					roi = img[result["region"]["y"]:result["region"]["y"]+result["region"]["h"], result["region"]["x"]:result["region"]["x"]+result["region"]["w"]]
					roi = cv2.cvtColor(roi, cv2.COLOR_BGR2GRAY)

					roi = cv2.resize(roi, (48, 48))

					if len(previous) == 0:
						emotions = result["emotion"].keys()
						previous = []
						for i in range(len(emotions)):
							previous.append(int(time.time()))
							previousPrediction.append(0)
					i = 0
					for emotion in emotions:

						# Numa fase inicial temos de OBRIGAR o dataset de neutralidade aumentar
						percentageEmotion = round(np.double(result["emotion"][emotion]), 4)
						if len(framesDominantAccuraciesTop10Emotions) != len(emotions):
							framesDominantAccuraciesTop10Emotions.append([])
							framesPredictionsTop10Emotions.append([])
							orderedPredictionsTop10Emotions.append([])
							times.append([])
							arrayTopFramePaths.append([])
						
						if emotion == "neutral" or result["dominant_emotion"] == emotion:
							framesDominantAccuraciesTop10Emotions[i], framesPredictionsTop10Emotions[i], orderedPredictionsTop10Emotions[i], previous[i], previousPrediction[i], times[i], arrayTopFramePaths[i] = processTopFrames(percentageEmotion, framesDominantAccuraciesTop10Emotions[i], framesPredictionsTop10Emotions[i], orderedPredictionsTop10Emotions[i], emotion, roi,  previous[i], previousPrediction[i], result["emotion"], times[i], arrayTopFramePaths[i])

						i = i + 1
			except Exception as e:
				ws.send(MAC_ADDRESS+";"+sys.argv[0]+";"+"Error: "+str(e)+";"+CLIENT_EMAIL)
			
			#this is the part where we display the output to the user
			#cv2.imshow('video', frame)
			#cv2.imshow('board', board)
			key=cv2.waitKey(1) 
			if key==ord('q'):   # here we are specifying the key which will stop the loop and stop all the processes going
				break
		
		"""Código de conecção à API"""
		# Windows slash bars wrong way => w.replace(os.sep, '/')
		# emotionsPath = [w.replace(os.sep, '/') for w in glob(TOP_FRAMES_PATH+'/*')]

		emotionsPath = []
		for emotion in emotions:
			emotionsPath.append(TOP_FRAMES_PATH+"/"+emotion)

		# defining the api-endpoint 
		API_ENDPOINT = API_URL+"/frames/upload"

		requestOk = 0
		requestTotal = 0
		i = 0
		for emotionPath in emotionsPath:
			emotion = emotionPath.split('/')[-1]
			if len(glob(TOP_FRAMES_PATH+'/'+emotion+'/*')) == 0:
				continue
			requestTotal = requestTotal + 1
			data = {
				"macAddress": MAC_ADDRESS,
				"emotion": emotion,
				"datesFrames": [],
				"accuraciesFrames": framesDominantAccuraciesTop10Emotions[i],
				"preditionsFrames": framesPredictionsTop10Emotions[i]
			}

			files = []
			imagesPath = arrayTopFramePaths[i]

			k = 0
			for imagePath in imagesPath:
				date = datetime.fromtimestamp(times[i][k]).strftime("%Y-%m-%d %H:%M:%S")
				data["datesFrames"].append(date)

				fileFrame = open(imagePath, 'rb')
				
				files.append(('file', (imagePath.split('/')[-1], fileFrame, 'application/octet-stream')))

				k = k + 1

			i = i + 1

			headers = {"Authorization": "Bearer "+token}

			# sending post request and saving response as response object
			r = requests.request("POST", API_ENDPOINT, headers=headers, data=data, files=files)

			if r.status_code == 200:
				requestOk = requestOk + 1
			# extracting response text 
			#responseIteration = r.json()
		if requestOk == requestTotal:
			print("Foi efetuado registo de frames com sucesso")
		else:
			ws.send(MAC_ADDRESS+";"+sys.argv[0]+";"+"Ocorreu um erro no registo de frames"+";"+CLIENT_EMAIL)
		time.sleep(1)	
		for file in files:
			file[1][1].close()

		# Variáveis POPable
		framesDominantAccuraciesTop10Emotions = []
		framesPredictionsTop10Emotions = []
		times = []
		arrayTopFramePaths = []

		orderedPredictionsTop10Emotions = []
		resetFolderFrames()

	#video.release()
else:
	print("Erro ao realizar Login com a sua conta")