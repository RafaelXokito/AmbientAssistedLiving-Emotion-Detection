import os
import gdown
from pathlib import Path
import zipfile
import numpy as np
import functions
import pandas as pd
import matplotlib.pyplot as plt
import tensorflow as tf
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
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.applications.vgg16 import VGG16 as PreTrainedModel, \
    preprocess_input

datasetPath = 'dataset'
#datasetPath = 'FER-2013'

train_path = datasetPath+'/train'#'dataset/train'
valid_path = datasetPath+'/test'#'dataset/test'

IMAGE_SIZE = [48,48]

batch_size = 128

images_files = glob(train_path+'/*/*.*')
valid_images_files = glob(valid_path+'/*/*.*')
folder = len(glob(train_path+'/*'))

#url = 'https://drive.google.com/uc?id=13iUHHP3SlNg53qSuQZDdHDSDNdBP9nwy'

def loadModel(url = 'https://github.com/serengil/deepface_models/releases/download/v1.0/facial_expression_model_weights.h5'):

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
    x = Dense(folder, activation='softmax')(x)  

    model = Model(inputs = ptm.input, outputs=x)

    model.compile(optimizer='adam', 
                    loss='categorical_crossentropy', 
                    metrics=['accuracy'])

    image_generator = ImageDataGenerator(rotation_range=30,
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
    
    valid_generator = image_generator.flow_from_directory(
                                                        valid_path,
                                                        target_size=IMAGE_SIZE, 
                                                        color_mode="grayscale",
                                                        batch_size=batch_size)

    r = model.fit_generator(
        train_generator,
        validation_data=valid_generator,
        epochs=10,
        steps_per_epoch = int(np.ceil(len(images_files)/ batch_size)),
        validation_steps = int(np.ceil(len(valid_images_files)/ batch_size)),
    )

    # convert the training history to a dataframe
    history_df = pd.DataFrame(r.history)
    # use Pandas native plot method
    history_df.loc[:, ['loss', 'val_loss']].plot()
    history_df.loc[5:, ['accuracy', 'val_accuracy']].plot()
    plt.show()
    
    return model