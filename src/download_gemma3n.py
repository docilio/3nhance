from transformers import AutoModelForCausalLM, AutoTokenizer

model_id = "google/gemma-3n-e2b-it"  # Or whichever model you're using

# This will force download and cache everything in ~/.cache/huggingface
print("Downloading model and tokenizer...")
tokenizer = AutoTokenizer.from_pretrained(model_id)
model = AutoModelForCausalLM.from_pretrained(model_id)
print("✅ Download completed")

