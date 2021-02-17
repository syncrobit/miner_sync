#!/bin/bash


#Install dependecies
echo "Installing Dependecies..."
sudo apt install curl wget jq -y
echo " "
echo " "

#Make Sync Request...
echo "Grabbing data..."
data=$(curl -s https://msync.syncrob.it/)
miner_version=$(jq -r '.minerRelease' <<< $data)
block_height=$(jq -r '.blockHeight' <<< $data)

echo " "
echo " "
echo "Miner Release: $miner_version"
echo "Snapshot Block Height: $block_height"
echo " "
echo " "

#Download file
echo "Downloading File..."
file=$(jq -r '.fileUri' <<< $data)
wget -O snapshot.bin ${file} -q --show-progress

#Verify downloaded file CheckSum
echo "Verifying downloaded file integrity..."
check_sum=$(jq -r '.checkSum.md5' <<< $data)
d_check_sum=$(md5sum snapshot.bin | awk '{print $1}')

if [ "$d_check_sum" = "$check_sum" ];
then
    echo "Checksum PASS."
else
    echo "Checksum FAIL."
    exit  
fi

echo " "
echo " "

#Restore SnapShot
echo "Copying & Restoring snapshot..."
cp snapshot.bin /home/pi/miner_data/snapshot.bin
docker exec miner miner snapshot load /var/data/snapshot.bin

echo " "
echo " "

echo "All Done!"