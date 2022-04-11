import os
from os.path import exists
import gdown
from pathlib import Path
import zipfile
import numpy as np
import functions
import pandas as pd
import matplotlib.pyplot as plt
import tensorflow as tf
import json
tf_version = int(tf.__version__.split(".")[0])

if tf_version == 1:
	import keras
	from keras.models import Model, Sequential
	from keras.layers import Conv2D, MaxPooling2D, AveragePooling2D, Flatten, Dense, Dropout
elif tf_version == 2:
	from tensorflow import keras
	from tensorflow.keras.models import Model, Sequential
	from tensorflow.keras.layers import Conv2D, MaxPooling2D, AveragePooling2D, Flatten, Dense, Dropout
from glob import glob
import datetime

# Eu meti este código no if em cima estava a dar erro

if tf_version == 1:
    from keras import callbacks
    from keras.models import load_model
if tf_version == 2:
    from tensorflow.keras import callbacks
    from tensorflow.keras.models import load_model

from tensorflow.keras.preprocessing.image import ImageDataGenerator

#url = 'https://drive.google.com/uc?id=13iUHHP3SlNg53qSuQZDdHDSDNdBP9nwy'

class_indices = {}
if exists('analysis/class_indices.json'):
    with open('analysis/class_indices.json') as json_file:
        class_indices = json.load(json_file)
else:
    class_indices = {'negative': 0, 'neutral': 1, 'positive': 2} # Default com três classes

def loadModel(url = 'https://github.com/serengil/deepface_models/releases/download/v1.0/facial_expression_model_weights.h5', dataset_dir = 'FER-2013',modelPath='weights/DeepFace_v6_binary_500_128.h5', classIndicesPath='analysis/class_indices.json', forceRetrain = False, epochs=100, batch_size=128, mode='categorical'):
    
    if exists(modelPath) and forceRetrain == False:
        with open(classIndicesPath) as json_file:
            class_indices = json.load(json_file)
        return load_model(modelPath), class_indices

    if mode=='categorical':
        activation = 'softmax'
        output = folder
        loss = 'categorical_crossentropy'
        metrics = ['accuracy']
    else:
        activation = 'sigmoid'    
        output = 1
        loss = 'binary_crossentropy'
        metrics = ['binary_accuracy']
        
    train_path = dataset_dir+'/train'
    valid_path = dataset_dir+'/test'

    IMAGE_SIZE = [48,48]

    images_files = glob(train_path+'/*/*.*')
    valid_images_files = glob(valid_path+'/*/*.*')
    folder = len(glob(train_path+'/*'))

    ptm = Sequential()
    num_classes = 7
    #1st convolution layer
    ptm.add(Conv2D(64, (5, 5), activation='relu', input_shape=(48,48,1)))
    ptm.add(MaxPooling2D(pool_size=(5,5), strides=(2, 2)))

    #2nd convolution layer
    ptm.add(Conv2D(64, (3, 3), activation='relu'))
    ptm.add(Conv2D(64, (3, 3), activation='relu'))
    ptm.add(AveragePooling2D(pool_size=(3,3), strides=(2, 2)))

    #3rd convolution layer
    ptm.add(Conv2D(128, (3, 3), activation='relu'))
    ptm.add(Conv2D(128, (3, 3), activation='relu'))
    ptm.add(AveragePooling2D(pool_size=(3,3), strides=(2, 2)))

    ptm.add(Flatten())

    #fully connected neural networks
    ptm.add(Dense(1024, activation='relu'))
    ptm.add(Dropout(0.2))
    ptm.add(Dense(1024, activation='relu'))
    ptm.add(Dropout(0.2))

    ptm.add(Dense(num_classes, activation='softmax'))

    #----------------------------

    home = functions.get_deepface_home()

    if os.path.isfile(home+'/.deepface/weights/facial_expression_model_weights.h5') != True:
        print("facial_expression_model_weights.h5 will be downloaded...")

        output = home+'/.deepface/weights/facial_expression_model_weights.h5'
        gdown.download(url, output, quiet=False)

    # Carregamento do pré-treino
    ptm.load_weights(home+'/.deepface/weights/facial_expression_model_weights.h5')
    
    # ----------------------- 
    # Transfer Learning

    ptm.trainable = False

    # Add new layers
    x = Flatten()(ptm.output)
    x = Dense(output, activation=activation)(x)  

    model = Model(inputs = ptm.input, outputs=x)

    model.compile(optimizer='adam', 
                    loss=loss, 
                    metrics=metrics)

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
                                                        color_mode="grayscale",
                                                        batch_size=batch_size)
    
    class_indices = train_generator.class_indices
    
    with open('analysis/class_indices.json', 'w', encoding='utf-8') as f:
        json.dump(class_indices, f, ensure_ascii=False, indent=4)
    
    valid_generator = image_generator.flow_from_directory(
                                                        valid_path,
                                                        target_size=IMAGE_SIZE, 
                                                        color_mode="grayscale",
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
        epochs=epochs,
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

    if mode == 'categorical':
        params = ['accuracy', 'val_accuracy']
    else:
        params = ['binary_accuracy', 'val_binary_accuracy'] 

    history_df.loc[:, params].plot()
    plt.show()

    return model,class_indices

def getClassIndices():
    return class_indices