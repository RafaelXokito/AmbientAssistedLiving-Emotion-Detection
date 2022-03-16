from ast import If
from time import sleep
import cv2
from cv2 import sqrt
import dlib
from matplotlib.pyplot import axis
import numpy as np
import math
import os
import pandas as pd

import matplotlib.pyplot as plt
import seaborn as sns

from math import sin, cos, radians

from sklearn import svm
from sklearn.metrics import mean_absolute_error
from sklearn.model_selection import train_test_split
from sqlalchemy import desc

from sklearn.inspection import permutation_importance
import matplotlib.pyplot as plt

import eli5
from eli5.sklearn import PermutationImportance

ESC=27

img_size = 224

important_landmarks = [19,21,22,24,48,51,54,57]
limitFilesPerFolder = 50

settings = {
    'scaleFactor': 1.3, 
    'minNeighbors': 3, 
    'minSize': (50, 50)
}

def vconcat_resize_min(im_list, interpolation=cv2.INTER_CUBIC):
    w_min = min(im.shape[1] for im in im_list)
    im_list_resize = [cv2.resize(im, (w_min, int(im.shape[0] * w_min / im.shape[1])), interpolation=interpolation)
                      for im in im_list]
    return cv2.vconcat(im_list_resize)

def rotate_image(image, angle):
    if angle == 0: return image
    height, width = image.shape[:2]
    rot_mat = cv2.getRotationMatrix2D((width/2, height/2), angle, 0.9)
    result = cv2.warpAffine(image, rot_mat, (width, height), flags=cv2.INTER_LINEAR)
    return result

def rotate_point(pos, img, angle):
    if angle == 0: return pos
    x = pos[0] - img.shape[1]*0.4
    y = pos[1] - img.shape[0]*0.4
    newx = x*cos(radians(angle)) + y*sin(radians(angle)) + img.shape[1]*0.4
    newy = -x*sin(radians(angle)) + y*cos(radians(angle)) + img.shape[0]*0.4
    return int(newx), int(newy), pos[2], pos[3]

def crop_faces(file_path,trainPath):
    
    emotion_data = pd.read_csv(file_path)
    
    y = emotion_data["emotion_ordinal"]

    feature_names = []
    for landmark in important_landmarks:
        feature_names.append("magnitude"+str(landmark))
        feature_names.append("direction"+str(landmark))
    
    X = emotion_data[feature_names]
    print(X.describe())

    train_X, val_X, train_y, val_y = train_test_split(X, y, random_state=0)

    model = svm.SVC()
    model.fit(train_X,train_y)

    perm_importance = permutation_importance(model, val_X,val_y,n_repeats=30, random_state=0)
    features = np.array(feature_names)
    
    sorted_idx = perm_importance.importances_mean.argsort()
    plt.barh(features[sorted_idx], perm_importance.importances_mean[sorted_idx])
    plt.xlabel("Permutation Importance")
    plt.show()

    perm = PermutationImportance(model, random_state=1).fit(val_X, val_y)
    print(eli5.format_as_text(eli5.explain_weights(perm,top=100,feature_names=feature_names)))


    predictions = model.predict(val_X)

    print(mean_absolute_error(val_y, predictions))

    # inicializa o systema de video para captura 
    cap = cv2.VideoCapture(0)
    
    labels = next(os.walk(trainPath))[1]

    face = cv2.CascadeClassifier("haarcascade_frontalface_alt2.xml")

    # Inicializa o detetor de rostos
    detector = dlib.get_frontal_face_detector()
    # Inicializa o previsor de landmarks nos rostos
    predictor = dlib.shape_predictor('shape_predictor_68_face_landmarks.dat')

    resized_img = []
    previous_emotion = ""
    img_emotion = np.ones(shape=[100,img_size,3], dtype=np.uint8)

    while True:
        # Ler imagem de input
        ret, img = cap.read()

        # Detetar os rostos
        for angle in [0, -25, 25]:
            rimg = rotate_image(img, angle)
            # deteção de rostos por Haarcascade
            detected = face.detectMultiScale(rimg, **settings)
            if len(detected):
                detected = [rotate_point(detected[-1], img, -angle)]
                break
        
        # Make a copy as we don't want to draw on the original image:
        for x, y, w, h in detected[-1:]:
            roi_color = img[y:y+h, x:x+w]

            # Converte para escala de cinza
            gray = cv2.cvtColor(roi_color, cv2.COLOR_BGR2GRAY)
            equalized = cv2.equalizeHist(gray)

            resized_img = cv2.resize(equalized, (img_size, img_size)) # Reshaping images to preferred size

            # Deteta os rostos da imagem cortada
            faces = detector(resized_img)

            for iface in faces:
                x1 = iface.left() # left point
                y1 = iface.top() # top point
                x2 = iface.right() # right point
                y2 = iface.bottom() # bottom point

                # Coordenadas do ponto central
                xc = ((x2-x1)/2)+x1
                yc = ((y2-y1)/2)+y1

                # Look for the landmarks
                landmarks = predictor(image=resized_img, box=iface)

                # vetor de caracteristicas
                feature_vector = []
                for n in important_landmarks:
                    xl = landmarks.part(n).x-x1
                    yl = landmarks.part(n).y-y1

                    cv2.circle(img=resized_img, center=(xl+x1, yl+y1), radius=1, color=(0, 255, 0), thickness=-1)
                    
                    # Calcular a distâcia (Teorema de Pitágoras) => c^2 = a^2 + b^2
                    a = xl - xc
                    b = yl - yc
                    c = round(math.sqrt((a*a)+(b*b)), 2)
                    distance = c

                    # Calcular a direção
                    if (xc == x):
                        direction = 0
                    else:
                        direction = math.degrees(math.atan((yc-yl)/(xc-xl)))

                    #feature_vector.append(xl)
                    #feature_vector.append(yl)
                    feature_vector.append(distance)
                    feature_vector.append(direction)
            
                predictions = model.predict([feature_vector])
                print(labels[int(predictions)])
            cv2.rectangle(img, (x, y), (x+w, y+h), (255,0,0), 4)
        
        # Output visual
        # Resize da imagem e tonalização em tons de cinza (o concat do cv2 apenas permite concat com tons igual)
        img2 = cv2.resize(cv2.cvtColor(img, cv2.COLOR_BGR2GRAY), (img_size, img_size)) # Reshaping images to preferred size
        # Se já foi detetado algum rosto?
        if len(resized_img) > 0:
            # Se foi detetado alguma emoção diferente
            if(labels[int(predictions)] != previous_emotion):
                label = labels[int(predictions)]

                # Preenchemos o "quadro" a branco para escrever a emoção nova
                img_emotion.fill(255) # or img[:] = 255
                
                # Escrevemos a emoção nova
                cv2.putText(img_emotion, label, (50, 50), cv2.FONT_HERSHEY_COMPLEX, 0.30, (0,255,0), 1)
                previous_emotion = label
            
            # Juntar todas as imagens
            img2 = cv2.vconcat([img2, cv2.cvtColor(img_emotion, cv2.COLOR_BGR2GRAY)])
            vis = vconcat_resize_min([resized_img, img2])
            cv2.imshow('Ambient Assisted Living', vis)
        else: # Se ainda não foi detetada a imagem
            cv2.imshow('Ambient Assisted Living', img2)
        
        # Termina com ESC pressionado
        k = cv2.waitKey(30) & 0xff
        if(k == ESC):
            break

    # Termina o systema de video
    cap.release()
    cv2.destroyAllWindows()
    return img

def add_photo(path = ""):
    img = []
    if path == "":
        # inicializa o systema de video para captura 
        cap = cv2.VideoCapture(0)

        while True:
            # Captura um frame
            ret, img = cap.read()

            # Janela de video com o resultado
            cv2.imshow('img', img)
            
            # Termina com ESC pressionado
            k = cv2.waitKey(30) & 0xff
            if(k == ESC):
                break
    
        cap.release()
        cv2.destroyAllWindows()
        img = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        img = cv2.resize(img,(640,490))
    else:
        img = cv2.imread(path)
        img = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        img = cv2.resize(img,(640,490))
    
    return img

def analyse_dataset(data_dir):
    from tqdm import tqdm

    data = []

    # Vai buscar as labels consuante a pasta de treinamento
    labels = next(os.walk(data_dir))[1]
    
    # Inicializa o detetor de rostos
    detector = dlib.get_frontal_face_detector()
    # Inicializa o previsor de landmarks nos rostos
    predictor = dlib.shape_predictor('shape_predictor_68_face_landmarks.dat')

    face = cv2.CascadeClassifier("haarcascade_frontalface_alt2.xml")

    img_map = np.ones(shape=[img_size+200,img_size+100,3], dtype=np.uint8)
    img_map.fill(255) # or img[:] = 255

    for label in labels:
        # Set the color of each label
        color = list(np.random.random(size=3) * 256)
        
        path = os.path.join(data_dir, label)

        # O target vai conter uma lista restrita de valores que correspondem à emoção
        class_num = labels.index(label)

        cv2.putText(img_map, label, (int((class_num*50)+10), int(img_size+150)), cv2.FONT_HERSHEY_COMPLEX, 0.30, color, 1)

        for img in tqdm(os.listdir(path)[:limitFilesPerFolder], desc=label+" "+str(class_num+1)+"/"+str(len(labels))):
            try:
                img = cv2.imread(os.path.join(path, img))

                # Detetar os rostos
                for angle in [0, -25, 25]:
                    rimg = rotate_image(img, angle)
                    # deteção de rostos por Haarcascade
                    detected = face.detectMultiScale(rimg, **settings)
                    if len(detected):
                        detected = [rotate_point(detected[-1], img, -angle)]
                        break
                
                # Percorrer cada rosto
                for x, y, w, h in detected[-1:]:
                    roi_color = img[y:y+h, x:x+w]

                    # Converte para escala de cinza
                    gray = cv2.cvtColor(roi_color, cv2.COLOR_BGR2GRAY)
                    equalized = cv2.equalizeHist(gray)

                    resized_img = cv2.resize(equalized, (img_size, img_size)) # Reshaping images to preferred size

                    # Deteta os rostos da imagem cortada
                    faces = detector(resized_img)

                    # Por cada face do RoI (é sempre uma)
                    for iface in faces[-1:]:
                        x1 = iface.left() # left point
                        y1 = iface.top() # top point
                        x2 = iface.right() # right point
                        y2 = iface.bottom() # bottom point

                        # Coordenadas do ponto central
                        xc = ((x2-x1)/2)+x1
                        yc = ((y2-y1)/2)+y1

                        # Look for the landmarks
                        landmarks = predictor(image=resized_img, box=iface)

                        # vetor de caracteristicas
                        feature_vector = []
                        for n in range(0,68):
                            xl = landmarks.part(n).x-x1
                            yl = landmarks.part(n).y-y1

                            cv2.circle(img=img_map, center=(xl+50, yl+50), radius=1, color=color, thickness=-1)
                            #cv2.circle(img=resized_img, center=(xl+x1, yl+y1), radius=1, color=(0, 255, 0), thickness=-1)
                            
                            # Calcular a distâcia (Teorema de Pitágoras) => c^2 = a^2 + b^2
                            a = xl - xc
                            b = yl - yc
                            c = round(math.sqrt((a*a)+(b*b)), 2)
                            distance = c

                            # Calcular a direção
                            if (xc == x):
                                direction = 0
                            else:
                                direction = math.degrees(math.atan((yc-yl)/(xc-xl)))

                            # Preenchimento do vetor de características
                            feature_vector.append(xl)
                            feature_vector.append(yl)
                            feature_vector.append(distance)
                            feature_vector.append(direction)

                        feature_vector.append(class_num)
                        feature_vector.append(label)
                        data.append(feature_vector)
                    
            except Exception as e:
                print(e)
    
    cv2.imshow('Mapping Landmarks', img_map)

    # Criar os headers
    header = []
    for i in range(68):
        header.append("point"+str(i+1)+".x")
        header.append("point"+str(i+1)+".y")
        header.append("magnitude"+str(i+1))
        header.append("direction"+str(i+1))
    header.append("emotion_ordinal")
    header.append("emotion")
    
    #Criar o csv
    df = pd.DataFrame(data)
    df.columns = header
    df.to_csv(data_dir+'/train_data.csv', index = False, header  = header)
    
    df["mouthOpenDistance"] = df["magnitude57"]-df["magnitude51"]

    sns.set_theme(style="ticks", color_codes=True)
    sns.set_palette("Paired")
    sns.scatterplot(data = df, x = "emotion", y = "mouthOpenDistance").set(title='Abertura da boca')

    plt.show()
    cv2.waitKey(0)
    cv2.destroyAllWindows()
    return data_dir+'/train_data.csv'