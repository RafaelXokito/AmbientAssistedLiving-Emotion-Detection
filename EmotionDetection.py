import warnings
warnings.filterwarnings("ignore")

import sys

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

	mode: # Mode for model compiler
	--mode categorical
	--mode binary

	epochs: # How many epochs
	-e 1
	-e 2
	-e 3
	...
	-e N

	batches: # Number of inputs for epoch
	-e 1
	-e 2
	-e 3
	...
	-e N

	forceRetrain: # Force retraining model
	-f

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
		if arg == '-f' or arg == '--force':
			forceRetrain = True
		if arg == '-a':
			activation = str(sys.argv[i+1]) 	
		if arg == '-l':
			loss = str(sys.argv[i+1])
		if arg == '--metrics':
			metrics = str(sys.argv[i+1])
		if arg == '-e' or arg == '-b' or arg == '-r':
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

import os
#os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

from os import path
import numpy as np
import pandas as pd
from tqdm import tqdm

import EmotionDeepFace
import EmotionVGG
import EmotionVGGFace
import EmotionFaceNet
import EmotionOpenFace
import functions

import tensorflow as tf
tf_version = int(tf.__version__.split(".")[0])
if tf_version == 2:
	import logging
	tf.get_logger().setLevel(logging.ERROR)


class_indices = EmotionDeepFace.getClassIndices()

def build_model(model_name, dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics):
	print("ola")
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

def analyze(img_path, actions = ('emotion', 'age', 'gender', 'race') , model = None, enforce_detection = True, detector_backend = 'opencv', prog_bar = False, dataset_dir = 'FER-2013', modelPath='', classIndicesPath='analysis/class_indices.json', forceRetrain=False, epochs=100, batches=128, activation='softmax', loss='categorical_crossentropy', metrics='accuracy'):

	"""
	This function analyzes facial attributes including age, gender, emotion and race

	Parameters:
		img_path: exact image path, numpy array or base64 encoded image could be passed. If you are going to analyze lots of images, then set this to list. e.g. img_path = ['img1.jpg', 'img2.jpg']

		actions (tuple): The default is ('age', 'gender', 'emotion', 'race'). You can drop some of those attributes.

		models: (Optional[dict]) facial attribute analysis models are built in every call of analyze function. You can pass pre-built models to speed the function up.

			models = {}
			models['age'] = DeepFace.build_model('Age')
			models['gender'] = DeepFace.build_model('Gender')
			model = DeepFace.build_model('Emotion')
			models['race'] = DeepFace.build_model('Race')

		enforce_detection (boolean): The function throws exception if a face could not be detected. Set this to True if you don't want to get exception. This might be convenient for low resolution images.

		detector_backend (string): set face detector backend as retinaface, mtcnn, opencv, ssd or dlib.

		prog_bar (boolean): enable/disable a progress bar
	Returns:
		The function returns a dictionary. If img_path is a list, then it will return list of dictionary.

		{
			"region": {'x': 230, 'y': 120, 'w': 36, 'h': 45},
			"age": 28.66,
			"gender": "woman",
			"dominant_emotion": "neutral",
			"emotion": {
				'sad': 37.65260875225067,
				'angry': 0.15512987738475204,
				'surprise': 0.0022171278033056296,
				'fear': 1.2489334680140018,
				'happy': 4.609785228967667,
				'disgust': 9.698561953541684e-07,
				'neutral': 56.33133053779602
			}
			"dominant_race": "white",
			"race": {
				'indian': 0.5480832420289516,
				'asian': 0.7830780930817127,
				'latino hispanic': 2.0677512511610985,
				'black': 0.06337375962175429,
				'middle eastern': 3.088453598320484,
				'white': 93.44925880432129
			}
		}

	"""

	actions = list(actions)

	img_paths, bulkProcess = functions.initialize_input(img_path)

	#---------------------------------

	built_models = ["emotion"] if model != None else []

	#---------------------------------

	#pre-trained models passed but it doesn't exist in actions
	if len(built_models) > 0:
		if 'emotion' in built_models and 'emotionDeepFace' not in actions:
			actions.append('emotionDeepFace')
		elif 'emotion' in built_models and 'emotionVGG16' not in actions:
			actions.append('emotionVGG16')
		elif 'emotion' in built_models and 'emotionVGGFace' not in actions:
			actions.append('emotionVGGFace')
		elif 'emotion' in built_models and 'emotionFaceNet' not in actions:
			actions.append('emotionFaceNet')
		elif 'emotion' in built_models and 'emotionOpenFace' not in actions:
			actions.append('emotionOpenFace')	
	#---------------------------------

	if 'emotionDeepFace' in actions and 'emotion' not in built_models:
		model = build_model('EmotionDeepFace', dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics)
	elif 'emotionVGG16' in actions and 'emotion' not in built_models:
		model = build_model('EmotionVGG16', dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics) 
	elif 'emotionVGGFace' in actions and 'emotion' not in built_models:
		model = build_model('EmotionVGGFace', dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics)   
	elif 'emotionFaceNet' in actions and 'emotion' not in built_models:
		model = build_model('EmotionFaceNet', dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics)   
	elif 'emotionOpenFace' in actions and 'emotion' not in built_models:
		model = build_model('EmotionOpenFace', dataset_dir, modelPath,classIndicesPath,forceRetrain, epochs, batches, activation, loss, metrics)   
	#---------------------------------

	resp_objects = []

	disable_option = (False if len(img_paths) > 1 else True) or not prog_bar

	global_pbar = tqdm(range(0,len(img_paths)), desc='Analyzing', disable = disable_option)

	for j in global_pbar:
		img_path = img_paths[j]

		resp_obj = {}

		disable_option = (False if len(actions) > 1 else True) or not prog_bar

		pbar = tqdm(range(0, len(actions)), desc='Finding actions', disable = disable_option)

		img_224 = None # Set to prevent re-detection

		region = [] # x, y, w, h of the detected face region
		region_labels = ['x', 'y', 'w', 'h']

		is_region_set = False

		#facial attribute analysis
		for index in pbar:
			action = actions[index]
			pbar.set_description("Action: %s" % (action))

			emotion_labels = list(class_indices.keys())

			img, region = functions.preprocess_face(img = img_path, target_size = (48, 48), grayscale = True, enforce_detection = enforce_detection, detector_backend = detector_backend, return_region = True)
			
			if img == []:
				resp_obj["emotion"] = {}
				for i in range(0, len(emotion_labels)):
					emotion_label = emotion_labels[i]
					emotion_prediction = 0
				
				resp_obj["dominant_emotion"] = "Not Found"
				break
		
			emotion_predictions = model.predict(img)[0,:]

			sum_of_predictions = emotion_predictions.sum()

			resp_obj["emotion"] = {}

			for i in range(0, len(emotion_labels)):
				emotion_label = emotion_labels[i]
				emotion_prediction = 100 * emotion_predictions[i] / sum_of_predictions
				resp_obj["emotion"][emotion_label] = emotion_prediction

			resp_obj["dominant_emotion"] = emotion_labels[np.argmax(emotion_predictions)]

			#-----------------------------
			
			if is_region_set != True:
				resp_obj["region"] = {}
				is_region_set = True
				for i, parameter in enumerate(region_labels):
					resp_obj["region"][parameter] = int(region[i]) #int cast is for the exception - object of type 'float32' is not JSON serializable

		#---------------------------------

		if bulkProcess == True:
			resp_objects.append(resp_obj)
		else:
			return resp_obj

	if bulkProcess == True:

		resp_obj = {}

		for i in range(0, len(resp_objects)):
			resp_item = resp_objects[i]
			resp_obj["instance_%d" % (i+1)] = resp_item

		return resp_obj

def main():
	#---------------------------
	#main
	
	#validar os parametros
	params = parameters()
	
	import matplotlib.pyplot as plt
	from tensorflow.keras.preprocessing import image

	import glob


	datasetPath = 'FER-2013'

	df_all = pd.DataFrame()

	# Este teste é baseado em validar a eficácia de um modelo treinado com o dataset FER-2013
	# testando com o dataset CK+
	# /Volumes/Extension/AmbientAssistedLiving/Images/042-ll042
	filesNames = glob.glob('dataset/*/*/*.*') # Todas as imagens de todas as classes de treino e validação
	#filesNames = glob.glob('/Volumes/Extension/AmbientAssistedLiving/Images/*/*/*.png') # Todas as imagens do dataset PAINFUL

	# Calculate the accuracy
	count = 0
	correct = 0

	countPositive = 0
	correctPositive = 0

	countNegative = 0
	correctNegative = 0

	model = params[0]
	run = params[1]
	epochs = params[2]
	batches = params[3]
	forceRetrain = params[4]
	activationFunction = params[5]
	lossFunction = params[6]
	metrics = params[7]

	if metrics == 'binary_accuracy':
		mode = 'binary'
	else:
		mode = 'categorical'

	for filename in filesNames:
		#img = cv2.imread(filename) # ler a imagem
		

		result = analyze(
			filename,
			actions = ['emotion'+str(model)],
			dataset_dir=datasetPath, 
			modelPath='weights/'+str(model)+'_v'+str(run)+'_'+mode+'_'+str(epochs)+'_'+str(batches)+'.h5',
			classIndicesPath='analysis/class_indices.json',
			forceRetrain=forceRetrain,
			epochs=epochs,
			batches=batches,
			activation=activationFunction,
			loss=lossFunction,
			metrics=metrics
		)
		
		# Acrescentar a label da imagem
		result["label"] = "positive" if "positive" in filename else "negative"
		result["imagePath"] = filename
		
		count = count + 1	
		if result["label"] == result["dominant_emotion"]:
			correct = correct + 1
		
		if result["label"] == 'positive':
			countPositive = countPositive + 1
			if result["label"] == result["dominant_emotion"]:
				correctPositive = correctPositive + 1
		
		if result["label"] == 'negative':
			countNegative = countNegative + 1
			if result["label"] == result["dominant_emotion"]:
				correctNegative = correctNegative + 1
		
		df = pd.json_normalize(result)

		if df_all.empty:
			df_all = df
		else:
			df_all = df_all.append(df)
		
	df_all.to_csv('analysis/'+str(model)+'_v'+str(run)+'_Analysis.csv', encoding='utf-8')

	# Data analysis
	print("Accuracy: "+str(round(correct/count,2)*100)+"%")

	print("Positive accuracy: "+str(round(correctPositive/countPositive,2)*100)+"%")
	print("Negative accuracy: "+str(round(correctNegative/countNegative,2)*100)+"%")

	import matplotlib.pyplot as plt

	data = {
		'Overall': round(correct/count,2)*100, 
		'Positive': round(correctPositive/countPositive,2)*100, 
		'Negative': round(correctNegative/countNegative,2)*100
	}

	names = list(data.keys())
	values = list(data.values())

	plt.scatter(names, values)
	plt.suptitle('Accuracy %')
	plt.show()



