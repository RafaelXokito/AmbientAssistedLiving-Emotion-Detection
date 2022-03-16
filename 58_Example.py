import cv2 as cv, sys, time, os, uuid
from face_detection import crop_faces,add_photo,analyse_dataset

trainPath = './input/TrainAAL'

try:

    print("\n\n*** Protótipo de deteção de rosto *** :)")
    print("\n\n*** MENU ***")
    print("*** [1] - Adicionar fotos ao dataset ***")
    print("*** [2] - Analisar o dataset e criar csv ***")
    print("*** [3] - Treinar e prever emoções ***")
    option = input("A sua escolha: ")
    if option == "1":
        while True:
            print("\n\n*** Adicionar fotos ao dataset ***")
            while True:
                print("\n\n*** MENU ***")
                print("*** [1] - Usar a câmara ***")
                print("*** [2] - Usar imagens de uma pasta ***")
                option = input("A sua escolha: ")
                if option == "1" or option == "2":
                    break
            if option == "1": # [1] - Usar a câmara
                print("\n\n*** NOVA CAPTURA DE IMAGEM ***")
                print("\nPrima ESC na janela grafica para capturar a nova imagem")
                fa = add_photo()
                print("Qual a emoção da foto?")
                directories = next(os.walk(trainPath))[1]
                for i in range(len(directories)):
                    print("["+str(i)+"] - "+directories[i])
                optionEmotion = int(input("A sua escolha: "),8)
                imageName = uuid.uuid4().hex+".png"
                cv.imwrite(trainPath+"/"+directories[optionEmotion]+"/"+imageName,fa)
                print("A imagem "+trainPath+"/"+directories[optionEmotion]+"/"+imageName+" foi gravada")
            if option == "2": # [2] - Usar imagens de uma pasta
                from tqdm import tqdm

                print("\n\n*** MENU ***")
                while True:
                    option = input("Folder Path: ")
                    if os.path.exists(option):
                        break
                
                print("Qual a emoção da pasta?")
                directories = next(os.walk(trainPath))[1]
                for i in range(len(directories)):
                    print("["+str(i)+"] - "+directories[i])
                optionEmotion = int(input("A sua escolha: "),8)
                
                for img in tqdm(os.listdir(option)):
                    try:
                        fa = add_photo(os.path.join(option, img))
                        
                        imageName = uuid.uuid4().hex+".png"
                        cv.imwrite(trainPath+"/"+directories[optionEmotion]+"/"+imageName,fa)
                    except Exception as e:
                        print(e)
    elif option == "2":
        analyse_dataset(trainPath)
    elif option == "3":
        while True:
            print("\n\n*** NOVA CAPTURA DE IMAGEM *** :)")
            print("\nPrima ESC na janela grafica para capturar a face")
            print("Prima CTRL+C na linha de comando para terminar o programa")
            fa = crop_faces(trainPath+'/train_data.csv', trainPath)
            print("A gravar a imagem faces.jpg")
            cv.imwrite("faces.jpg",fa)
            print("A aguardar 0.5 segundos até nova captura ou CTRL+C para terminar")
            time.sleep(0.5)
    
except:
    print("Ocorreu um erro", sys.exc_info())
finally:
    print("Fim do programa")
    cv.destroyAllWindows()

