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

    [Header("UI Botón de grabación")]
    [SerializeField] private RecordingButtonUI recordingButtonUI;

    [Header("Configuración de la obra")]
    [SerializeField] private int obraId = 1;

    [Header("Base de datos")]
    [SerializeField] private CommentUploader commentUploader;

    private AudioClip clip;
    private byte[] wavBytes;
    private bool recording;
    private string microphoneDevice;

    private bool audioEscuchado = false;

    #if UNITY_ANDROID && !UNITY_EDITOR
        private const int sampleRate = 48000;
    #else
        private const int sampleRate = 44100;
    #endif

    [Header("Duración máxima de grabación")]
    [SerializeField] private int maxRecordingSeconds = 120;

    private const string huggingFaceUrl =
        "https://router.huggingface.co/hf-inference/models/openai/whisper-large-v3-turbo";

    private void Start()
    {
        if (startButton != null)
        {
            startButton.onClick.AddListener(
                StartRecording
            );

            // No puede grabar hasta escuchar
            // el audio completo.
            startButton.interactable =
                audioEscuchado;
        }

        if (stopButton != null)
        {
            stopButton.onClick.AddListener(StopRecording);
            stopButton.interactable = false;
        }

        if (resultText != null)
        {
            resultText.text = audioEscuchado ? "Presiona grabar" : "Escucha primero el audio";
        }

        ComprobarMicrofono();
    }

    private void ComprobarMicrofono()
    {
#if UNITY_ANDROID && !UNITY_EDITOR
    // En la APK de Quest usamos directamente el micrófono del propio visor.
    microphoneDevice = null;
#else
        // En Unity Editor / Windows buscamos automáticamente el dispositivo de Quest Link.
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

        // Si no encuentra Oculus, usa el primer micrófono disponible.
        if (string.IsNullOrEmpty(microphoneDevice))
        {
            microphoneDevice = Microphone.devices[0];
        }
#endif
    }

    private void Update()
    {
        if (!recording || clip == null)
            return;

        int position =
            Microphone.GetPosition(microphoneDevice);

        if (position >= clip.samples - 1)
            StopRecording();
    }

    public void HabilitarGrabacionTrasAudio()
    {
        audioEscuchado = true;

        if (startButton != null)
        {
            startButton.interactable = true;
        }

        if (resultText != null)
        {
            resultText.text = "Presiona grabar";
        }

        if (recordingButtonUI != null)
        {
            recordingButtonUI.MostrarNormal();
        }
    }

    public void StartRecording()
    {
        if (!audioEscuchado)
        {
            if (resultText != null)
                resultText.text = "Escucha primero el audio completo";

            return;
        }

        if (recording)
            return;

        StartCoroutine(PrepararYGrabar());
    }

    private IEnumerator PrepararYGrabar()
    {
#if UNITY_ANDROID && !UNITY_EDITOR

    // ==========================================
    // COMPROBAR PERMISO
    // ==========================================

    if (
        !Permission.HasUserAuthorizedPermission(
            Permission.Microphone
        )
    )
    {
        if (resultText != null)
        {
            resultText.text =
                "Es necesario permitir el micrófono";
        }

        Permission.RequestUserPermission(
            Permission.Microphone
        );

        yield break;
    }


    // ==========================================
    // COMPROBAR SI QUEST HA MUTEADO EL MICRO
    // ==========================================

    if (
        QuestMicrophoneManager
            .MicrofonoSistemaMuteado()
    )
    {
        if (resultText != null)
        {
            resultText.text =
                "El micrófono está silenciado. Actívalo en las Quest.";
        }

        yield break;
    }

#endif


        yield return IniciarMicrofono();
    }

    private IEnumerator IniciarMicrofono()
    {
        // Volvemos a buscar el dispositivo cada vez
        ComprobarMicrofono();

#if !UNITY_ANDROID || UNITY_EDITOR

        if (string.IsNullOrEmpty(microphoneDevice))
        {
            if (resultText != null)
                resultText.text = "No se ha detectado micrófono";

            yield break;
        }

#endif

        // DEBUG TEMPORAL 1
        Debug.Log("MICROFONO USADO: " + microphoneDevice);

        // Liberar cualquier sesión anterior del mismo micrófono
        if (Microphone.IsRecording(microphoneDevice))
        {
            Microphone.End(microphoneDevice);
        }

        clip = null;

        // Esperar un poco para que el dispositivo de Oculus se libere
        yield return new WaitForSecondsRealtime(0.3f);

        clip = Microphone.Start(
            microphoneDevice,
            false,
            maxRecordingSeconds,
            sampleRate
        );

        if (clip == null)
        {
            if (resultText != null)
                resultText.text = "Error al iniciar micrófono";

            yield break;
        }

        // Esperar a que realmente empiece la captura
        float tiempoEspera = 0f;

        while (
            Microphone.GetPosition(microphoneDevice) <= 0 &&
            tiempoEspera < 2f
        )
        {
            tiempoEspera += Time.unscaledDeltaTime;
            yield return null;
        }

        if (Microphone.GetPosition(microphoneDevice) <= 0)
        {
            Microphone.End(microphoneDevice);

            clip = null;

            if (resultText != null)
                resultText.text = "Error al iniciar micrófono";

            yield break;
        }

        recording = true;

        if (recordingButtonUI != null)
            recordingButtonUI.MostrarGrabando();

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

        int position =
            Microphone.GetPosition(microphoneDevice);

        // DEBUG TEMPORAL 2
        Debug.Log("MUESTRAS CAPTURADAS: " + position);

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

            if (recordingButtonUI != null)
                recordingButtonUI.MostrarNormal();

            return;
        }

        if (resultText != null)
            resultText.text = "Procesando audio...";

        if (!EncodeAsWAV(position))
        {
            if (resultText != null)
                resultText.text = "No se ha detectado voz";

            if (recordingButtonUI != null)
                recordingButtonUI.MostrarNormal();

            return;
        }

        StartCoroutine(EnviarAHuggingFace());
    }

    private bool EncodeAsWAV(int length)
    {
        int channels = clip.channels;

        float[] samples =
            new float[length * channels];

        if (!clip.GetData(samples, 0))
            return false;


        // ==========================================
        // COMPROBAR QUE REALMENTE HAY VOZ
        // ==========================================

        float maxAmplitude = 0f;
        double sumaCuadrados = 0.0;

        for (int i = 0; i < samples.Length; i++)
        {
            float valor = samples[i];
            float absoluto = Mathf.Abs(valor);

            if (absoluto > maxAmplitude)
            {
                maxAmplitude = absoluto;
            }

            sumaCuadrados += valor * valor;
        }


        float rms =
            Mathf.Sqrt(
                (float)(
                    sumaCuadrados /
                    samples.Length
                )
            );


        Debug.Log(
            "AMPLITUD MAXIMA: " +
            maxAmplitude
        );

        Debug.Log(
            "RMS AUDIO: " +
            rms
        );


        // Si solo hay silencio o ruido muy pequeño,
        // no lo enviamos a Whisper.
        if (
            maxAmplitude < 0.005f ||
            rms < 0.0015f
        )
        {
            Debug.LogWarning(
                "AUDIO DESCARTADO | Max: " +
                maxAmplitude +
                " | RMS: " +
                rms
            );

            return false;
        }


        // ==========================================
        // CREAR WAV
        // ==========================================

        using (MemoryStream memoryStream = new MemoryStream())
        {
            using (BinaryWriter writer = new BinaryWriter(memoryStream))
            {
                writer.Write(
                    Encoding.ASCII.GetBytes("RIFF")
                );

                writer.Write(
                    36 + samples.Length * 2
                );

                writer.Write(
                    Encoding.ASCII.GetBytes("WAVE")
                );

                writer.Write(
                    Encoding.ASCII.GetBytes("fmt ")
                );

                writer.Write(16);
                writer.Write((ushort)1);
                writer.Write((ushort)channels);
                writer.Write(clip.frequency);

                writer.Write(
                    clip.frequency *
                    channels *
                    2
                );

                writer.Write(
                    (ushort)(channels * 2)
                );

                writer.Write((ushort)16);

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

        return true;
    }

    private IEnumerator EnviarAHuggingFace()
    {
        HFSecrets secrets =
            Resources.Load<HFSecrets>(
                "Local/HFSecrets"
            );

        if (
            secrets == null ||
            string.IsNullOrWhiteSpace(
                secrets.huggingFaceToken
            )
        )
        {
            if (resultText != null)
                resultText.text =
                    "Error de configuración";

            if (recordingButtonUI != null)
                recordingButtonUI.MostrarNormal();

            yield break;
        }

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
                "Bearer " +
                secrets.huggingFaceToken
            );

            request.SetRequestHeader(
                "Content-Type",
                "audio/wav"
            );

            yield return
                request.SendWebRequest();

            if (
                request.result !=
                UnityWebRequest.Result.Success
            )
            {
                if (resultText != null)
                    resultText.text =
                        "Error procesando audio";

                if (recordingButtonUI != null)
                    recordingButtonUI.MostrarNormal();

                yield break;
            }

            string textoExtraido =
                ExtraerTextoDeJson(
                    request.downloadHandler.text
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

                if (recordingButtonUI != null)
                    recordingButtonUI.MostrarNormal();

                yield break;
            }

            textoExtraido =
                textoExtraido.Trim();

            if (resultText != null)
                resultText.text =
                    textoExtraido;

            if (commentUploader != null)
            {
                commentUploader.GuardarComentario(
                    obraId,
                    textoExtraido
                );
            }

            if (recordingButtonUI != null)
                recordingButtonUI.MostrarGracias();
        }
    }

    private string ExtraerTextoDeJson(string json)
    {
        if (json.Contains("\"text\":\""))
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