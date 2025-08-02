import base64
import requests

def read_image_as_base64(image_path):
    with open(image_path, 'rb') as img_file:
        return base64.b64encode(img_file.read()).decode('utf-8')

def send_image_to_gemma(image_path, prompt):
    image_b64 = read_image_as_base64(image_path)
    payload = {
        "model": "gemma3:4b",
        "prompt": prompt,
        "images": [image_b64],
        "stream": False
    }

    response = requests.post("http://localhost:11434/api/generate", json=payload)

    if response.status_code == 200:
        return response.json()['response']
    else:
        raise Exception(f"Error: {response.status_code} - {response.text}")

# Example usage
if __name__ == "__main__":
    image_path = "../demo/sample_passport.jpg"  # Replace with your image path
    prompt = "Export all relevant text of this image and place them into a json array (field, value)"
    
    try:
        result = send_image_to_gemma(image_path, prompt)
        print("Gemma's Response:\n", result)
    except Exception as e:
        print("Failed to process image:", e)

