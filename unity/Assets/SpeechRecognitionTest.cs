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

    private const string huggingFaceUrl =
        "https://router.huggingface.co/hf-inference/models/openai/whisper-large-v3-turbo";

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
            Debug.LogWarning("No hay permiso de micrófono. Solicitándolo...");
            Permission.RequestUserPermission(Permission.Microphone);
        }
#endif

        if (Microphone.devices.Length == 0)
        {
            Debug.LogError("NO SE HA DETECTADO NINGÚN MICRÓFONO.");

            if (resultText != null)
                resultText.text = "No se ha detectado micrófono";

            return;
        }

        Debug.Log("MICRÓFONOS DETECTADOS: " + Microphone.devices.Length);

        for (int i = 0; i < Microphone.devices.Length; i++)
        {
            Debug.Log(
                "Micrófono " +
                i +
                ": " +
                Microphone.devices[i]
            );
        }

        microphoneDevice = Microphone.devices[3];

        Debug.Log(
            "MICRÓFONO SELECCIONADO: " +
            microphoneDevice
        );
    }

    private void Update()
    {
        if (!recording || clip == null)
            return;

        int position =
            Microphone.GetPosition(microphoneDevice);

        if (position >= clip.samples - 1)
        {
            StopRecording();
        }
    }

    public void StartRecording()
    {
        if (recording)
            return;

        if (Microphone.devices.Length == 0)
        {
            ComprobarMicrofono();
            return;
        }

        if (string.IsNullOrEmpty(microphoneDevice))
            microphoneDevice = Microphone.devices[3];

        Debug.Log(
            "INICIANDO GRABACIÓN CON: " +
            microphoneDevice
        );

        clip = Microphone.Start(
            microphoneDevice,
            false,
            maxRecordingSeconds,
            sampleRate
        );

        if (clip == null)
        {
            Debug.LogError(
                "Microphone.Start ha devuelto NULL."
            );

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

        StartCoroutine(
            ComprobarInicioMicrofono()
        );
    }

    private IEnumerator ComprobarInicioMicrofono()
    {
        float timeout = 2f;
        float tiempo = 0f;

        while (
            Microphone.GetPosition(microphoneDevice) <= 0 &&
            tiempo < timeout
        )
        {
            tiempo += Time.deltaTime;
            yield return null;
        }

        int posicion =
            Microphone.GetPosition(microphoneDevice);

        Debug.Log(
            "POSICIÓN DEL MICRÓFONO DESPUÉS DE INICIAR: " +
            posicion
        );

        if (posicion <= 0)
        {
            Debug.LogError(
                "EL MICRÓFONO NO HA EMPEZADO A CAPTURAR AUDIO."
            );
        }
    }

    public void StopRecording()
    {
        if (!recording || clip == null)
            return;

        int position =
            Microphone.GetPosition(microphoneDevice);

        Microphone.End(microphoneDevice);

        recording = false;

        if (startButton != null)
            startButton.interactable = true;

        if (stopButton != null)
            stopButton.interactable = false;

        Debug.Log(
            "GRABACIÓN DETENIDA. MUESTRAS: " +
            position
        );

        if (position <= 0)
        {
            Debug.LogError(
                "NO SE HAN CAPTURADO MUESTRAS."
            );

            if (resultText != null)
                resultText.text =
                    "No se ha detectado audio";

            return;
        }

        float duracion =
            (float)position / clip.frequency;

        Debug.Log(
            "DURACIÓN REAL DEL AUDIO: " +
            duracion.ToString("F2") +
            " segundos"
        );

        if (resultText != null)
            resultText.text =
                "Procesando audio...";

        bool audioValido =
            EncodeAsWAV(position);

        if (!audioValido)
        {
            if (resultText != null)
                resultText.text =
                    "No se ha detectado voz";

            return;
        }

        StartCoroutine(
            EnviarAHuggingFace()
        );
    }

    private bool EncodeAsWAV(int length)
    {
        int channels = clip.channels;

        float[] samples =
            new float[length * channels];

        bool datosCorrectos =
            clip.GetData(samples, 0);

        Debug.Log(
            "Clip frecuencia: " +
            clip.frequency
        );

        Debug.Log(
            "Clip canales: " +
            clip.channels
        );

        Debug.Log(
            "GetData correcto: " +
            datosCorrectos
        );

        float maxAmplitude = 0f;
        double sumaCuadrados = 0;

        for (int i = 0; i < samples.Length; i++)
        {
            float valor =
                Mathf.Abs(samples[i]);

            if (valor > maxAmplitude)
                maxAmplitude = valor;

            sumaCuadrados +=
                samples[i] * samples[i];
        }

        float rms =
            Mathf.Sqrt(
                (float)(
                    sumaCuadrados /
                    samples.Length
                )
            );

        Debug.Log(
            "AMPLITUD MÁXIMA: " +
            maxAmplitude
        );

        Debug.Log(
            "RMS AUDIO: " +
            rms
        );

        // Si obtenemos valores extremadamente bajos,
        // prácticamente estamos enviando silencio.
        if (maxAmplitude < 0.005f)
        {
            Debug.LogError(
                "EL AUDIO ES PRÁCTICAMENTE SILENCIO. " +
                "No se enviará a Hugging Face."
            );

            return false;
        }

        using (
            MemoryStream memoryStream =
                new MemoryStream()
        )
        {
            using (
                BinaryWriter writer =
                    new BinaryWriter(memoryStream)
            )
            {
                writer.Write(
                    Encoding.ASCII.GetBytes("RIFF")
                );

                writer.Write(
                    36 +
                    samples.Length * 2
                );

                writer.Write(
                    Encoding.ASCII.GetBytes("WAVE")
                );

                writer.Write(
                    Encoding.ASCII.GetBytes("fmt ")
                );

                writer.Write(16);

                writer.Write(
                    (ushort)1
                );

                writer.Write(
                    (ushort)channels
                );

                writer.Write(
                    clip.frequency
                );

                writer.Write(
                    clip.frequency *
                    channels *
                    2
                );

                writer.Write(
                    (ushort)(
                        channels * 2
                    )
                );

                writer.Write(
                    (ushort)16
                );

                writer.Write(
                    Encoding.ASCII.GetBytes("data")
                );

                writer.Write(
                    samples.Length * 2
                );

                foreach (float sample in samples)
                {
                    float limitado =
                        Mathf.Clamp(
                            sample,
                            -1f,
                            1f
                        );

                    writer.Write(
                        (short)(
                            limitado *
                            short.MaxValue
                        )
                    );
                }
            }

            wavBytes =
                memoryStream.ToArray();
        }

        Debug.Log(
            "TAMAÑO WAV: " +
            wavBytes.Length +
            " bytes"
        );

        return true;
    }

    private IEnumerator EnviarAHuggingFace()
    {
        // ================================
        // OBTENER TOKEN LOCAL
        // ================================

        HFSecrets secrets =
            Resources.Load<HFSecrets>(
                "Local/HFSecrets"
            );

        if (secrets == null)
        {
            Debug.LogError(
                "No se encontró HFSecrets.asset."
            );

            if (resultText != null)
                resultText.text =
                    "Error de configuración";

            yield break;
        }

        string apiKey =
            secrets.huggingFaceToken;

        if (string.IsNullOrWhiteSpace(apiKey))
        {
            Debug.LogError(
                "El token de Hugging Face está vacío."
            );

            if (resultText != null)
                resultText.text =
                    "Error de configuración";

            yield break;
        }

        Debug.Log(
            "ENVIANDO WAV A HUGGING FACE..."
        );

        using (
            UnityWebRequest request =
                new UnityWebRequest(
                    huggingFaceUrl,
                    "POST"
                )
        )
        {
            request.uploadHandler =
                new UploadHandlerRaw(
                    wavBytes
                );

            request.downloadHandler =
                new DownloadHandlerBuffer();

            request.SetRequestHeader(
                "Authorization",
                "Bearer " + apiKey
            );

            request.SetRequestHeader(
                "Content-Type",
                "audio/wav"
            );

            yield return
                request.SendWebRequest();

            if (
                request.result ==
                UnityWebRequest.Result.Success
            )
            {
                string jsonResponse =
                    request.downloadHandler.text;

                Debug.Log(
                    "RESPUESTA COMPLETA HF: " +
                    jsonResponse
                );

                string textoExtraido =
                    ExtraerTextoDeJson(
                        jsonResponse
                    );

                Debug.Log(
                    "TRANSCRIPCIÓN EXTRAÍDA: [" +
                    textoExtraido +
                    "]"
                );

                if (
                    string.IsNullOrWhiteSpace(
                        textoExtraido
                    )
                )
                {
                    if (resultText != null)
                        resultText.text =
                            "No se detectó texto";

                    yield break;
                }

                if (resultText != null)
                {
                    resultText.text =
                        textoExtraido;
                }

                // ==============================
                // GUARDAR EN BBDD
                // ==============================

                if (commentUploader == null)
                {
                    Debug.LogError(
                        "CommentUploader no está asignado."
                    );

                    yield break;
                }

                Debug.Log(
                    "ENVIANDO COMENTARIO A BBDD | " +
                    "Obra: " +
                    obraId +
                    " | Texto: " +
                    textoExtraido
                );

                commentUploader
                    .GuardarComentario(
                        obraId,
                        textoExtraido
                    );
            }
            else
            {
                Debug.LogError(
                    "ERROR HTTP: " +
                    request.error +
                    " | HTTP " +
                    request.responseCode +
                    " | " +
                    request.downloadHandler.text
                );

                if (resultText != null)
                    resultText.text =
                        "Error procesando audio";
            }
        }
    }

    private string ExtraerTextoDeJson(
        string json
    )
    {
        if (
            json.Contains(
                "\"text\":\""
            )
        )
        {
            int start =
                json.IndexOf(
                    "\"text\":\""
                ) + 8;

            int end =
                json.IndexOf(
                    "\"",
                    start
                );

            if (end > start)
            {
                return json.Substring(
                    start,
                    end - start
                );
            }
        }

        return "";
    }
}