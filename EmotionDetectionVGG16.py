from re import X
from xml.etree.ElementInclude import include
import tensorflow as tf
print(tf.__version__)

from tensorflow.keras.layers import Input, Dense, Flatten
from tensorflow.keras.applications.vgg16 import VGG16 as PretrainedModel, preprocess_input
from tensorflow.keras.models import Model
from tensorflow.keras.optimizers import SGD, Adam
from tensorflow.keras.preprocessing import image
from tensorflow.keras.preprocessing.image import ImageDataGenerator

from glob import glob

import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import sys, os

train_path = 'input/created_dataset/Train'
valid_path = 'input/created_dataset/Test'

image_size = [224,224]

image_files = glob(train_path + '/*/*.*')
valid_image_files = glob(valid_path + '/*/*.*')

folders = glob(train_path + '/*')

ptm = PretrainedModel(
    input_shape = image_size+[3],
    weights = 'imagenet',
    include_top = False
)

ptm.trainable = False

K = len(folders)
x = Flatten()(ptm.output)
x = Dense(K, activation='softmax')(x)

model = Model(inputs=ptm.input, outputs=x)

image_generator = ImageDataGenerator(
    rotation_range=20,
    width_shift_range=0.1,
    height_shift_range=0.1,
    shear_range=0.1,
    zoom_range=0.2,
    horizontal_flip=True,
    preprocessing_function=preprocess_input)

batch_size = 128

train_generator = image_generator.flow_from_directory(
    train_path,
    shuffle=True, 
    target_size=image_size, 
    batch_size=batch_size)

valid_generator = image_generator.flow_from_directory(
    valid_path,
    target_size=image_size, 
    batch_size=batch_size)


model.compile(optimizer='adam', 
    loss='categorical_crossentropy', 
    metrics=['accuracy'])

r = model.fit_generator(
    train_generator,
    validation_data=valid_generator,
    epochs=10,
    steps_per_epoch = int(np.ceil(len(image_files)/ batch_size)),
    validation_steps = int(np.ceil(len(valid_image_files)/ batch_size)),
)

history_df = pd.DataFrame(r.history)
history_df.loc[:, ['loss', 'val_loss']].plot()
plt.show()




