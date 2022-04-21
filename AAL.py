import warnings
warnings.filterwarnings("ignore")

import sys

import EmotionDeepFace
import EmotionVGG
import EmotionVGGFace
import EmotionFaceNet
import EmotionOpenFace

import os
from glob import glob

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

		""")
		exit()

	if len(sys.argv) < 15 or len(sys.argv) > 16:
		print("Missing parameters, run -h or --help")
		exit()
	
	forceRetrain = False

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
	
	return model, run, epochs, batch, forceRetrain, activation, loss, metrics

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
		'EmotionVGG16': EmotionVGG.loadModel,
		'EmotionVGGFace': EmotionVGGFace.loadModel,
		'EmotionFaceNet': EmotionFaceNet.loadModel,
		'EmotionOpenFace': EmotionOpenFace.loadModel,
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


def processTopFrames(predictionEmotion, arrayTopPredictionsEmotion, emotion, frame):
	"""
	Order the top predictions array in descrescent order, of the first percentage is smaller than the 
	current frame's prediction we have to replace one with the other
	"""
	if os.path.exists('top10Frames') is False:
		os.mkdir('top10Frames')
	
	if os.path.exists('top10Frames/'+emotion) is False:
		os.mkdir('top10Frames/'+emotion)

	
	framesEmotions = glob('top10Frames/'+emotion+'/*.jpg')
	count = len(framesEmotions)
	if len(arrayTopPredictionsEmotion) >= 10:	
		orderedFramesPredictions = sorted(arrayTopPredictionsEmotion) 
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

	else:
		count = count + 1
		arrayTopPredictionsEmotion.append(predictionEmotion)		
		filePath = 'top10Frames/'+emotion+'/frame_'+str(count)+'.jpg'
		cv2.imwrite(filePath, frame)
	return arrayTopPredictionsEmotion


def resetFolderFrames(emotion):
	if os.path.exists('top10Frames/'+emotion) is True:
		files = glob('top10Frames/'+emotion+'/*.jpg')
		if len(files) > 0:
			for f in files:
				os.remove(f)
		return len(glob('top10Frames/'+emotion+'/*.jpg'))
	return -1

#validar os parametros
params = parameters()

import cv2
from EmotionDetection import analyze
import numpy as np

video=cv2.VideoCapture(0)  #requisting the input from the webcam or camera

model = params[0]
run = params[1]
epochs = params[2]
batches = params[3]
forceRetrain = params[4]
activationFunction = params[5]
lossFunction = params[6]
metrics = params[7]

datasetPath = 'FER-2013'

if metrics == 'binary_accuracy':
    mode = 'binary'
else:
    mode = 'categorical'

modelPath = 'weights/'+str(model)+'_v'+str(run)+'_'+mode+'_'+str(epochs)+'_'+str(batches)+'.h5'
classIndicesPath = 'analysis/class_indices.json'
model = build_model('EmotionDeepFace', datasetPath, modelPath,classIndicesPath,forceRetrain, epochs, batches, activationFunction, lossFunction, metrics)

board = np.ones(shape=[400,600,3], dtype=np.uint8)

# Preenchemos o "quadro" a branco para escrever a emoção nova
board.fill(0) # or img[:] = 255

cv2.putText(board, "Negative", (50, 200), cv2.FONT_HERSHEY_COMPLEX, 0.50, (0,255,0), 1)
centro_x = int(((440 - 140)/2)+140)
cv2.circle(board,(centro_x, 200),5,(0,0,255),-1)
cv2.putText(board, "Positive", (450, 200), cv2.FONT_HERSHEY_COMPLEX, 0.50, (0,255,0), 1)

x = 0

framesPredictionsTop10Positive = []
framesPredictionsTop10Negative = []
framesPredictionsTop10Neutral = []


resetFolderFrames('negative')
resetFolderFrames('positive')
resetFolderFrames('neutral')		

while True:  #checking if are getting video feed and using it
	_,frame = video.read()

	result = analyze(
		frame,
		model=model,
	)

	try:
		if result["dominant_emotion"] != "Not Found":
			img=cv2.rectangle(frame,(result["region"]["x"],result["region"]["y"]),(result["region"]["x"]+result["region"]["w"],result["region"]["y"]+result["region"]["h"]),(0,0,255),1)  
			
			p_negative = np.double(result["emotion"]["negative"])
			p_positive = np.double(result["emotion"]["positive"])
			p_neutral = np.double(result["emotion"]["neutral"])
			#print([p_negative,p_neutral,p_positive])
			cv2.circle(board,(x, 200),10,(0,0,0),-1)
			cv2.line(board,(140, 200),(440, 200),(0,255,0),1)

			x = centro_x - int(p_negative+p_neutral) if p_negative > p_positive else centro_x + int(p_positive+p_neutral)
			cv2.circle(board,(x, 200),10,(255,255,255),-1)

			if result["dominant_emotion"] == "negative":
				framesPredictionsTop10Negative = processTopFrames(round(p_negative, 4), framesPredictionsTop10Negative, "negative", frame)
			
			if result["dominant_emotion"] == "neutral":
				framesPredictionsTop10Neutral = processTopFrames(round(p_neutral, 4), framesPredictionsTop10Neutral, "neutral", frame)
			
			if result["dominant_emotion"] == "positive":
				framesPredictionsTop10Positive = processTopFrames(round(p_positive, 4), framesPredictionsTop10Positive, "positive", frame)
			
		
			#print(result["dominant_emotion"])  #here we will only go print out the dominant emotion also explained in the previous example
	except:
		print("no face")
	
	#this is the part where we display the output to the user
	#cv2.imshow('video', frame)
	cv2.imshow('board', board)
	key=cv2.waitKey(1) 
	if key==ord('q'):   # here we are specifying the key which will stop the loop and stop all the processes going
		break
video.release()

print(framesPredictionsTop10)