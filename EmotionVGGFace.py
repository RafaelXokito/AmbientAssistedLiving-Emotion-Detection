import os
from pathlib import Path
import gdown
from os.path import exists
import json
import numpy as np
from deepface.commons import functions
import pandas as pd
import matplotlib.pyplot as plt

import tensorflow as tf
tf_version = int(tf.__version__.split(".")[0])

if tf_version == 1:
	from keras.models import Model, Sequential
	from keras.layers import Input, Convolution2D, ZeroPadding2D, MaxPooling2D, Flatten, Dense, Dropout, Activation
else:
	from tensorflow import keras
	from tensorflow.keras.models import Model, Sequential
	from tensorflow.keras.layers import Input, Convolution2D, ZeroPadding2D, MaxPooling2D, Flatten, Dense, Dropout, Activation

from glob import glob
import datetime
from tensorflow.keras.preprocessing.image import ImageDataGenerator

# Eu meti este código no if em cima estava a dar erro

if tf_version == 1:
    from keras import callbacks
    from keras.models import load_model
if tf_version == 2:
    from tensorflow.keras import callbacks
    from tensorflow.keras.models import load_model

#---------------------------------------
class_indices = {}
if exists('analysis/class_indices.json'):
    with open('analysis/class_indices.json') as json_file:
        class_indices = json.load(json_file)
else:
    class_indices = {'negative': 0, 'neutral': 1, 'positive': 2} # Default com três classes

#---------------------------------------

def baseModel():
	model = Sequential()
	model.add(ZeroPadding2D((1,1),input_shape=(224,224, 3)))
	model.add(Convolution2D(64, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(64, (3, 3), activation='relu'))
	model.add(MaxPooling2D((2,2), strides=(2,2)))

	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(128, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(128, (3, 3), activation='relu'))
	model.add(MaxPooling2D((2,2), strides=(2,2)))

	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(256, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(256, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(256, (3, 3), activation='relu'))
	model.add(MaxPooling2D((2,2), strides=(2,2)))

	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(512, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(512, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(512, (3, 3), activation='relu'))
	model.add(MaxPooling2D((2,2), strides=(2,2)))

	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(512, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(512, (3, 3), activation='relu'))
	model.add(ZeroPadding2D((1,1)))
	model.add(Convolution2D(512, (3, 3), activation='relu'))
	model.add(MaxPooling2D((2,2), strides=(2,2)))

	model.add(Convolution2D(4096, (7, 7), activation='relu'))
	model.add(Dropout(0.5))
	model.add(Convolution2D(4096, (1, 1), activation='relu'))
	model.add(Dropout(0.5))
	model.add(Convolution2D(2622, (1, 1)))
	model.add(Flatten())
	model.add(Activation('softmax'))

	return model

#url = 'https://drive.google.com/uc?id=1CPSeum3HpopfomUEK1gybeuIVoeJT_Eo'

def loadModel(url = 'https://github.com/serengil/deepface_models/releases/download/v1.0/vgg_face_weights.h5', dataset_dir = 'FER-2013',modelPath='weights/DeepFace_v6_binary_500_128.h5', classIndicesPath='analysis/class_indices.json', forceRetrain = False):
    
    if exists(modelPath) and forceRetrain == False:
        with open(classIndicesPath) as json_file:
            class_indices = json.load(json_file)
        return load_model(modelPath), class_indices

    train_path = dataset_dir+'/train'
    valid_path = dataset_dir+'/test'

    IMAGE_SIZE = [224,224]

    batch_size = 128

    images_files = glob(train_path+'/*/*.*')
    valid_images_files = glob(valid_path+'/*/*.*')
    folder = len(glob(train_path+'/*'))

    ptm = baseModel()

	#-----------------------------------

    home = functions.get_deepface_home()
    output = home+'/.deepface/weights/vgg_face_weights.h5'

    if os.path.isfile(output) != True:
        print("vgg_face_weights.h5 will be downloaded...")
        gdown.download(url, output, quiet=False)

    #-----------------------------------

    ptm.load_weights(output)


    #-----------------------------------
    # Transfer Learning

    ptm.trainable = False

    # Add new layers
    x = Flatten()(ptm.output)
    x = Dense(folder, activation='sigmoid')(x)  

    model = Model(inputs = ptm.input, outputs=x)

    model.compile(optimizer='adam', 
                    loss='binary_crossentropy', 
                    metrics=['binary_accuracy'])

    image_generator = ImageDataGenerator(
        rotation_range=30,
        width_shift_range=0.1,
        height_shift_range=0.1,
        shear_range=0.1,
        zoom_range=0.2,
        horizontal_flip=True,
        brightness_range=[0.4,1.5]
        )#preprocessing_function=preprocess_input

    train_generator = image_generator.flow_from_directory(
                                                        train_path,
                                                        shuffle=True, 
                                                        target_size=IMAGE_SIZE, 
                                                        #color_mode="grayscale",
                                                        batch_size=batch_size)

    class_indices = train_generator.class_indices

    with open('analysis/class_indices.json', 'w', encoding='utf-8') as f:
        json.dump(class_indices, f, ensure_ascii=False, indent=4)

    valid_generator = image_generator.flow_from_directory(
                                                        valid_path,
                                                        target_size=IMAGE_SIZE, 
                                                        #color_mode="grayscale",
                                                        batch_size=batch_size)

    early_stopping = callbacks.EarlyStopping(
        min_delta=0.001, # minimium amount of change to count as an improvement
        patience=20, # how many epochs to wait before stopping
        restore_best_weights=True,
    )

    # Get the time before train
    before = datetime.datetime.now()

    r = model.fit_generator(
        train_generator,
        validation_data=valid_generator,
        epochs=500,
        steps_per_epoch = int(np.ceil(len(images_files)/ batch_size)),
        validation_steps = int(np.ceil(len(valid_images_files)/ batch_size)),
        callbacks=[early_stopping],
    )

    # Get the time after train
    after = datetime.datetime.now()
    diffTime = after - before
    print("Time training: "+str(diffTime.seconds)+" seconds\n")

    model.save(filepath=modelPath,include_optimizer=True)

    # convert the training history to a dataframe
    history_df = pd.DataFrame(r.history)
    # use Pandas native plot method
    history_df.loc[:, ['loss', 'val_loss']].plot()
    history_df.loc[:, ['binary_accuracy', 'val_binary_accuracy']].plot()
    plt.show()

    return model,class_indices

    #TO-DO: why?
    #vgg_face_descriptor = Model(inputs=model.layers[0].input, outputs=model.layers[-2].output)

    #return vgg_face_descriptor