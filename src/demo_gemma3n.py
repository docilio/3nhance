from transformers import AutoProcessor, Gemma3nForConditionalGeneration
from PIL import Image
import torch
import textwrap

# 1) Load model & processor (CPU-only here)
model_id = "google/gemma-3n-e2b-it"
processor = AutoProcessor.from_pretrained(model_id)
model = Gemma3nForConditionalGeneration.from_pretrained(
    model_id,
    torch_dtype=torch.float32,
    device_map="cpu"
).eval()

# 2) Helper to pretty-print
def print_response(text: str):
    for line in text.split("\n"):
        print(textwrap.fill(line, 100))

# 3) Predict function (follows official sample)
def predict(messages):
    # This packs both image+text into inputs
    inputs = processor.apply_chat_template(
        messages,
        add_generation_prompt=True,
        tokenize=True,
        return_dict=True,
        return_tensors="pt",
    ).to(model.device)

    # remember how many tokens the prompt was
    input_len = inputs["input_ids"].shape[-1]

    with torch.inference_mode():
        gen = model.generate(**inputs, max_new_tokens=500, do_sample=False)
    # strip off the prompt
    gen = gen[0][input_len:]

    return processor.decode(gen, skip_special_tokens=True)

# 4) Load your image
img = Image.open("../demo/sample_passport.jpg").convert("RGB")

# 5) Build the single user message
messages = [
    {
        "role": "user",
        "content": [
            {"type": "image", "image": img},
            {
                "type": "text",
                "text": "List any visible fields (using JSON array with field,value)."
            },
        ],
    }
]

# 6) Run & print
response = predict(messages)
print_response(response)

