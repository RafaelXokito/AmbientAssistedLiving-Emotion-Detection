#! /bin/bash
echo "Starting startup config..."
echo "This startup is based on the following video configuration -> https://www.youtube.com/watch?v=vekblEk6UPc"

echo "Checking OS and architecture..."
uname -m
cat /etc/os-release

echo "Checking Python version..."
python -V

echo "Creating environment..."
mkdir project
cd project
sudo apt-get install python3-pip
python -m pip install virtualenv
python -m virtualenv env
source env/bin/activate

echo "Installing dependencies..."
sudo apt-get install -y libhdf5-dev libc-ares-dev libeigen3-dev gcc gfortran libgfortran5 libatlas3-base libatlas-base-dev libopenblas-dev libopenblas-base libblas-dev liblapack-dev cython3 libatlas-base-dev openmpi-bin libopenmpi-dev python3-dev
pip install -U wheel mock six

echo "Downloading .sh file from PINTO0309 repository..."
wget https://raw.githubusercontent.com/PINTO0309/Tensorflow-bin/main/previous_versions/download_tensorflow-2.8.0-cp39-none-linux_aarch64_numpy1221.sh

echo "Make .sh file executable..."
sudo chmod +x download_tensorflow-2.8.0-cp39-none-linux_aarch64_numpy1221.sh

echo "Executing .sh file..."
./download_tensorflow-2.8.0-cp39-none-linux_aarch64_numpy1221.sh

echo "Make sure we dont have tensorflow installed..."
sudo pip uninstall tensorflow
pip uninstall tensorflow

echo "Installing tensorflow..."
python -m pip install --upgrade pip
pip install tensorflow-2.8.0-cp39-none-linux_aarch64.whl
pip install 'protobuf<=3.20.1' --force-reinstall

echo "Testing tensorflow..."
python -c 'import tensorflow as tf;print(tf.__version__)'

echo "Installing open-cv"
pip install opencv-python
pip install opencv-python-headless

echo "Testing open-cv"
python -c 'import cv2;print(cv2.__version__)'

echo "Installing AALHome dependencies"
pip install websocket-client
pip install rel
pip install python-dotenv 
pip install gdown
pip install pandas
pip install opencv-python
pip install Pillow
pip install deepface
pip install matplotlib
pip install getmac
pip install SciPy

done