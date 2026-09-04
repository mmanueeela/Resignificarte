using System.IO;
using System.Collections;
using System.Text;
using TMPro;
using UnityEngine;
using UnityEngine.UI;
using UnityEngine.Networking;

public class SpeechRecognitionTest : MonoBehaviour
{
    [Header("UI References")]
    [SerializeField] private Button startButton;
    [SerializeField] private Button stopButton;
    [SerializeField] private TextMeshProUGUI resultText;

    [Header("Hugging Face Config")]
    [SerializeField] private string apiKey = "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"; // Se puede poner aquí o en el Inspector

    private AudioClip clip;
    private byte[] wavBytes;
    private bool recording;

    private void Start()
    {
        if (startButton != null) startButton.onClick.AddListener(StartRecording);
        if (stopButton != null) stopButton.onClick.AddListener(StopRecording);
        if (stopButton != null) stopButton.interactable = false;
    }

    private void Update()
    {
        if (recording && Microphone.GetPosition(null) >= clip.samples)
        {
            StopRecording();
        }
    }

    private void StartRecording()
    {
        clip = Microphone.Start(null, false, 10, 44100);
        recording = true;
        if (startButton != null) startButton.interactable = false;
        if (stopButton != null) stopButton.interactable = true;
        if (resultText != null) resultText.text = "Escuchando...";
    }

    private void StopRecording()
    {
        var position = Microphone.GetPosition(null);
        Microphone.End(null);
        recording = false;

        if (startButton != null) startButton.interactable = true;
        if (stopButton != null) stopButton.interactable = false;

        if (resultText != null) resultText.text = "Procesando audio...";

        EncodeAsWAV(position);
        StartCoroutine(EnviarAHuggingFace());
    }

    private void EncodeAsWAV(int length)
    {
        var samples = new float[length * clip.channels];
        clip.GetData(samples, 0);

        using (var memoryStream = new MemoryStream())
        {
            using (var writer = new BinaryWriter(memoryStream))
            {
                writer.Write(Encoding.UTF8.GetBytes("RIFF"));
                writer.Write(36 + samples.Length * 2);
                writer.Write(Encoding.UTF8.GetBytes("WAVE"));
                writer.Write(Encoding.UTF8.GetBytes("fmt "));
                writer.Write(16);
                writer.Write((ushort)1);
                writer.Write((ushort)clip.channels);
                writer.Write(clip.frequency);
                writer.Write(clip.frequency * clip.channels * 2);
                writer.Write((ushort)(clip.channels * 2));
                writer.Write((ushort)16);
                writer.Write(Encoding.UTF8.GetBytes("data"));
                writer.Write(samples.Length * 2);

                foreach (var sample in samples)
                {
                    writer.Write((short)(sample * short.MaxValue));
                }
            }
            wavBytes = memoryStream.ToArray();
        }
    }

    private IEnumerator EnviarAHuggingFace()
    {
        // Router oficial actualizado de Hugging Face
        string url = "https://router.huggingface.co/hf-inference/models/openai/whisper-large-v3-turbo";

        using (UnityWebRequest request = new UnityWebRequest(url, "POST"))
        {
            request.uploadHandler = new UploadHandlerRaw(wavBytes);
            request.downloadHandler = new DownloadHandlerBuffer();
            request.SetRequestHeader("Authorization", "Bearer " + apiKey);
            request.SetRequestHeader("Content-Type", "audio/wav");

            yield return request.SendWebRequest();

            if (request.result == UnityWebRequest.Result.Success)
            {
                string jsonResponse = request.downloadHandler.text;
                string textoExtraido = ExtraerTextoDeJson(jsonResponse);

                if (resultText != null) resultText.text = textoExtraido;
                Debug.Log("Transcripción recibida: " + textoExtraido);
            }
            else
            {
                if (resultText != null) resultText.text = "Error: " + request.error;
                Debug.LogError("Error HTTP: " + request.error + " | " + request.downloadHandler.text);
            }
        }
    }

    private string ExtraerTextoDeJson(string json)
    {
        if (json.Contains("\"text\":\""))
        {
            int start = json.IndexOf("\"text\":\"") + 8;
            int end = json.IndexOf("\"", start);
            if (end > start) return json.Substring(start, end - start);
        }
        return json;
    }
}