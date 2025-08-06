docker volume create n8n_data

# You need to create a volume where the n8n_data will actually be. 
# This allows to update the versions without compromise the workflows and your data

mkdir -p ~/n8n_shared            # Creates a shared folder in your home directory


