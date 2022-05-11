from asyncio import subprocess
import warnings
warnings.filterwarnings("ignore")

import sys

import EmotionDeepFace

import os
import time
from datetime import datetime
import subprocess
import requests
from glob import glob

from dotenv import load_dotenv
load_dotenv()
import base64
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
			#print(model_name," built")
		else:
			raise ValueError('Invalid model_name passed - {}'.format(model_name))

	return model_obj[model_name]

def processTopFrames(predictionEmotion, arrayTopPredictionsEmotion, orderedFramesPredictions, emotion, frame):
	"""
	Order the top predictions array in descrescent order, of the first percentage is smaller than the 
	current frame's prediction we have to replace one with the other
	"""
	if os.path.exists('top10Frames') is False:
		os.mkdir('top10Frames')
	
	if os.path.exists('top10Frames/'+emotion) is False:
		os.mkdir('top10Frames/'+emotion)

	
	framesEmotions =  [w.replace(os.sep, '/') for w in  glob('top10Frames/'+emotion+'/*.jpg')]
	count = len(framesEmotions)
	if len(arrayTopPredictionsEmotion) >= 10:
		if orderedFramesPredictions[0] < predictionEmotion: 
			# finds original index of lower prediction value
			lowerValueIndex = arrayTopPredictionsEmotion.index(orderedFramesPredictions[0]) 
			# deletes the lower value from the top 10 array and folder
			arrayTopPredictionsEmotion.pop(lowerValueIndex) 
			filenameFrameLowerValue = framesEmotions[lowerValueIndex]
			if os.path.exists(filenameFrameLowerValue) is True:
				os.remove(filenameFrameLowerValue)

			# adds the new frame to the top 10 array and folder
			arrayTopPredictionsEmotion.append(predictionEmotion)
			filePath = filenameFrameLowerValue
			cv2.imwrite(filePath, frame)

			orderedFramesPredictions = sorted(arrayTopPredictionsEmotion) 
	else:
		count = count + 1
		arrayTopPredictionsEmotion.append(predictionEmotion)	

		filePath = 'top10Frames/'+emotion+'/frame_'+str(count)+'.jpg'
		cv2.imwrite(filePath, frame)

		orderedFramesPredictions = sorted(arrayTopPredictionsEmotion) 
	return arrayTopPredictionsEmotion, orderedFramesPredictions

def resetFolderFrames(emotion):
	if os.path.exists('top10Frames/'+emotion) is True:
		files =  [w.replace(os.sep, '/') for w in  glob('top10Frames/'+emotion+'/*.jpg')]		
		if len(files) > 0:
			for f in files:
				os.remove(f)
		return len(glob('top10Frames/'+emotion+'/*.jpg'))
	return -1

def creation_date(path_to_file):
    if platform.system() == 'Windows':
        return os.path.getctime(path_to_file)
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

	#board = np.ones(shape=[400,600,3], dtype=np.uint8)

	# Preenchemos o "quadro" a branco para escrever a emoção nova
	#board.fill(0) # or img[:] = 255

	#cv2.putText(board, "Negative", (50, 200), cv2.FONT_HERSHEY_COMPLEX, 0.50, (0,255,0), 1)
	centro_x = int(((440 - 140)/2)+140)
	#cv2.circle(board,(centro_x, 200),5,(0,0,255),-1)
	#cv2.putText(board, "Positive", (450, 200), cv2.FONT_HERSHEY_COMPLEX, 0.50, (0,255,0), 1)

	x = 0

	framesPredictionsTop10Positive = []
	orderedPredictionsTop10Positive = []
	framesPredictionsTop10Negative = []
	orderedPredictionsTop10Negative = []
	framesPredictionsTop10Neutral = []
	orderedPredictionsTop10Neutral = []


	resetFolderFrames('negative')
	resetFolderFrames('positive')
	resetFolderFrames('neutral')
	auxmodelCreationDate = creation_date(modelPath)
	while True:

		start_time = time.time()
		modelCreationDate = creation_date(modelPath)
		if auxmodelCreationDate != modelCreationDate:
			model = build_model('EmotionDeepFace', datasetPath, modelPath,classIndicesPath,False, epochs, batches, activationFunction, lossFunction, metrics)
		
		#Irá capturar vídeo até chegar ao tempo parametrizado (segundos) e fazer passar à fase de human labelling
		while ( int(time.time() - start_time) < time_HLIteration ):  #checking if are getting video feed and using it
			_,frame = video.read()

			try:
				result = analyze(
					frame,
					model=model,
				)

				if result["dominant_emotion"] != "Not Found":
					img=cv2.rectangle(frame,(result["region"]["x"],result["region"]["y"]),(result["region"]["x"]+result["region"]["w"],result["region"]["y"]+result["region"]["h"]),(0,0,255),1)  
					roi = img[result["region"]["y"]:result["region"]["y"]+result["region"]["h"], result["region"]["x"]:result["region"]["x"]+result["region"]["w"]]
					roi = cv2.cvtColor(roi, cv2.COLOR_BGR2GRAY)

					roi = cv2.resize(roi,(48,48))
					
					p_negative = np.double(result["emotion"]["negative"])
					p_positive = np.double(result["emotion"]["positive"])
					p_neutral = np.double(result["emotion"]["neutral"])

					#cv2.circle(board,(x, 200),10,(0,0,0),-1)
					#cv2.line(board,(140, 200),(440, 200),(0,255,0),1)

					x = centro_x - int(p_negative+p_neutral) if p_negative > p_positive else centro_x + int(p_positive+p_neutral)
					#cv2.circle(board,(x, 200),10,(255,255,255),-1)

					if result["dominant_emotion"] == "negative":
						framesPredictionsTop10Negative,orderedPredictionsTop10Negative = processTopFrames(round(p_negative, 4), framesPredictionsTop10Negative, orderedPredictionsTop10Negative, "negative", roi)
					
					#if result["dominant_emotion"] == "neutral":
					# Numa fase inicial temos de OBRIGAR o dataset de neutralidade aumentar
					framesPredictionsTop10Neutral,orderedPredictionsTop10Neutral = processTopFrames(round(p_neutral, 4), framesPredictionsTop10Neutral, orderedPredictionsTop10Neutral, "neutral", roi)
					
					if result["dominant_emotion"] == "positive":
						framesPredictionsTop10Positive,orderedPredictionsTop10Positive = processTopFrames(round(p_positive, 4), framesPredictionsTop10Positive, orderedPredictionsTop10Positive, "positive", roi)
				
						#print(result["dominant_emotion"], int(time.time() - start_time))  #here we will only go print out the dominant emotion also explained in the previous example
			except Exception as e:
				ws.send(MAC_ADDRESS+";"+sys.argv[0]+";"+"Error: "+e+";"+CLIENT_EMAIL)
			
			#this is the part where we display the output to the user
			#cv2.imshow('video', frame)
			#cv2.imshow('board', board)
			key=cv2.waitKey(1) 
			if key==ord('q'):   # here we are specifying the key which will stop the loop and stop all the processes going
				break
		
		"""Código de conecção à API"""
		# Windows slash bars wrong way => w.replace(os.sep, '/')
		emotionsPath =  [w.replace(os.sep, '/') for w in  glob(TOP_FRAMES_PATH+'/*')]

		# defining the api-endpoint 
		API_ENDPOINT = API_URL+"/frames/upload"

		requestOk = 0
		requestTotal = 0

		for emotionPath in emotionsPath:
			emotion = emotionPath.split('/')[-1]

			if len(glob(TOP_FRAMES_PATH+'/'+emotion+'/*')) == 0:
				continue
			
			requestTotal = requestTotal + 1

			data = {
				"macAddress": MAC_ADDRESS,
				"emotion": emotion,
				"datesFrames":[]
			}

			files = []
			imagesPath = [w.replace(os.sep, '/') for w in  glob(TOP_FRAMES_PATH+'/'+emotion+'/*')]
			for imagePath in imagesPath:				
				date = creation_date(imagePath)
				date = datetime.fromtimestamp(date).strftime("%Y-%m-%d %H:%M:%S")
				data["datesFrames"].append(date)
				fileFrame = open(imagePath,'rb')
				
				files.append(('file',(imagePath.split('/')[-1],fileFrame,'application/octet-stream')))
				
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

		framesPredictionsTop10Positive = []
		orderedPredictionsTop10Positive = []
		framesPredictionsTop10Negative = []
		orderedPredictionsTop10Negative = []
		framesPredictionsTop10Neutral = []
		orderedPredictionsTop10Neutral = []


		resetFolderFrames('negative')
		resetFolderFrames('positive')
		resetFolderFrames('neutral')
	#video.release()
else:
	print("Erro ao realizar Login com a sua conta")