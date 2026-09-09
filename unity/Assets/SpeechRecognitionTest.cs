using System.IO;
using System.Collections;
using System.Text;
using TMPro;
using UnityEngine;
using UnityEngine.UI;
using UnityEngine.Networking;

#if UNITY_ANDROID
using UnityEngine.Android;
#endif

public class SpeechRecognitionTest : MonoBehaviour
{
    [Header("UI References")]
    [SerializeField] private Button startButton;
    [SerializeField] private Button stopButton;
    [SerializeField] private TextMeshProUGUI resultText;

    [Header("Configuración de la obra")]
    [SerializeField] private int obraId = 1;

    [Header("Base de datos")]
    [SerializeField] private CommentUploader commentUploader;

    private AudioClip clip;
    private byte[] wavBytes;
    private bool recording;
    private string microphoneDevice;

    private const int sampleRate = 44100;
    private const int maxRecordingSeconds = 10;
    private const string huggingFaceUrl = "https://router.huggingface.co/hf-inference/models/openai/whisper-large-v3-turbo";

    private void Start()
    {
        if (startButton != null)
            startButton.onClick.AddListener(StartRecording);

        if (stopButton != null)
        {
            stopButton.onClick.AddListener(StopRecording);
            stopButton.interactable = false;
        }

        if (resultText != null)
            resultText.text = "Presiona grabar";

        ComprobarMicrofono();
    }

    private void ComprobarMicrofono()
    {
#if UNITY_ANDROID
        if (!Permission.HasUserAuthorizedPermission(Permission.Microphone))
        {
            Permission.RequestUserPermission(Permission.Microphone);
        }
#endif

        if (Microphone.devices.Length == 0)
        {
            microphoneDevice = null;
            if (resultText != null)
                resultText.text = "No se ha detectado micrófono";
            return;
        }

        microphoneDevice = null;

        foreach (string dispositivo in Microphone.devices)
        {
            if (dispositivo.IndexOf("Oculus Virtual Audio Device", System.StringComparison.OrdinalIgnoreCase) >= 0)
            {
                microphoneDevice = dispositivo;
                break;
            }
        }

        // En Quest Link utilizará Oculus.
        // En el APK de las Quest, si ese nombre no existe,
        // utilizará el micrófono disponible.
        if (string.IsNullOrEmpty(microphoneDevice))
            microphoneDevice = Microphone.devices[0];
    }

    private void Update()
    {
        if (!recording || clip == null)
            return;

        int position = Microphone.GetPosition(microphoneDevice);

        if (position >= clip.samples - 1)
            StopRecording();
    }

    public void StartRecording()
    {
        if (recording)
            return;

        if (string.IsNullOrEmpty(microphoneDevice))
        {
            ComprobarMicrofono();

            if (string.IsNullOrEmpty(microphoneDevice))
            {
                if (resultText != null)
                    resultText.text = "No se ha detectado micrófono";
                return;
            }
        }

        clip = Microphone.Start(microphoneDevice, false, maxRecordingSeconds, sampleRate);

        if (clip == null)
        {
            if (resultText != null)
                resultText.text = "Error al iniciar micrófono";
            return;
        }

        recording = true;

        if (startButton != null)
            startButton.interactable = false;

        if (stopButton != null)
            stopButton.interactable = true;

        if (resultText != null)
            resultText.text = "Escuchando...";
    }

    public void StopRecording()
    {
        if (!recording || clip == null)
            return;

        int position = Microphone.GetPosition(microphoneDevice);
        Microphone.End(microphoneDevice);
        recording = false;

        if (startButton != null)
            startButton.interactable = true;

        if (stopButton != null)
            stopButton.interactable = false;

        if (position <= 0)
        {
            if (resultText != null)
                resultText.text = "No se ha detectado audio";
            return;
        }

        if (resultText != null)
            resultText.text = "Procesando audio...";

        if (!EncodeAsWAV(position))
        {
            if (resultText != null)
                resultText.text = "No se ha detectado voz";
            return;
        }

        StartCoroutine(EnviarAHuggingFace());
    }

    private bool EncodeAsWAV(int length)
    {
        int channels = clip.channels;
        float[] samples = new float[length * channels];

        if (!clip.GetData(samples, 0))
            return false;

        float maxAmplitude = 0f;

        for (int i = 0; i < samples.Length; i++)
        {
            float valor = Mathf.Abs(samples[i]);
            if (valor > maxAmplitude)
                maxAmplitude = valor;
        }

        // Evita mandar silencio a Whisper.
        if (maxAmplitude < 0.005f)
            return false;

        using (MemoryStream memoryStream = new MemoryStream())
        {
            using (BinaryWriter writer = new BinaryWriter(memoryStream))
            {
                writer.Write(Encoding.ASCII.GetBytes("RIFF"));
                writer.Write(36 + samples.Length * 2);
                writer.Write(Encoding.ASCII.GetBytes("WAVE"));

                writer.Write(Encoding.ASCII.GetBytes("fmt "));
                writer.Write(16);
                writer.Write((ushort)1);
                writer.Write((ushort)channels);
                writer.Write(clip.frequency);
                writer.Write(clip.frequency * channels * 2);
                writer.Write((ushort)(channels * 2));
                writer.Write((ushort)16);

                writer.Write(Encoding.ASCII.GetBytes("data"));
                writer.Write(samples.Length * 2);

                foreach (float sample in samples)
                {
                    float limitado = Mathf.Clamp(sample, -1f, 1f);
                    writer.Write((short)(limitado * short.MaxValue));
                }
            }

            wavBytes = memoryStream.ToArray();
        }

        return true;
    }

    private IEnumerator EnviarAHuggingFace()
    {
        HFSecrets secrets = Resources.Load<HFSecrets>("Local/HFSecrets");

        if (secrets == null || string.IsNullOrWhiteSpace(secrets.huggingFaceToken))
        {
            if (resultText != null)
                resultText.text = "Error de configuración";
            yield break;
        }

        using (UnityWebRequest request = new UnityWebRequest(huggingFaceUrl, "POST"))
        {
            request.uploadHandler = new UploadHandlerRaw(wavBytes);
            request.downloadHandler = new DownloadHandlerBuffer();
            request.SetRequestHeader("Authorization", "Bearer " + secrets.huggingFaceToken);
            request.SetRequestHeader("Content-Type", "audio/wav");

            yield return request.SendWebRequest();

            if (request.result != UnityWebRequest.Result.Success)
            {
                if (resultText != null)
                    resultText.text = "Error procesando audio";
                yield break;
            }

            string textoExtraido = ExtraerTextoDeJson(request.downloadHandler.text);

            if (string.IsNullOrWhiteSpace(textoExtraido))
            {
                if (resultText != null)
                    resultText.text = "No se detectó texto";
                yield break;
            }

            textoExtraido = textoExtraido.Trim();

            if (resultText != null)
                resultText.text = textoExtraido;

            if (commentUploader != null)
            {
                commentUploader.GuardarComentario(obraId, textoExtraido);
            }
        }
    }

    private string ExtraerTextoDeJson(string json)
    {
        if (json.Contains("\"text\":\""))
        {
            int start = json.IndexOf("\"text\":\"") + 8;
            int end = json.IndexOf("\"", start);

            if (end > start)
                return json.Substring(start, end - start);
        }

        return "";
    }
}