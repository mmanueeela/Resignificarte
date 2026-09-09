using UnityEngine;
using TMPro;
using UnityEngine.Networking;
using UnityEngine.SceneManagement;
using System.Collections;

public class PhoneLoginManager : MonoBehaviour
{
    [SerializeField] private TextMeshProUGUI phoneDisplay;
    [SerializeField] private TextMeshProUGUI statusText;
    [SerializeField] private string apiUrl = "https://mzazzar.upv.edu.es/php/login_vr.php";

    private int maxDigits = 15;
    private string currentNumber = "";
    private bool loginEnProceso = false;

    void Start()
    {
        if (phoneDisplay == null)
        {
            GameObject phoneObject = GameObject.Find("PhoneDisplay_Text");

            if (phoneObject != null)
                phoneDisplay = phoneObject.GetComponent<TextMeshProUGUI>();
        }

        if (statusText == null)
        {
            GameObject statusObject = GameObject.Find("StatusDisplay_Text");

            if (statusObject != null)
                statusText = statusObject.GetComponent<TextMeshProUGUI>();
        }

        if (statusText != null)
            statusText.text = "";

        UpdateDisplay();
    }

    public void TypeDigit(string digit)
    {
        if (loginEnProceso)
            return;

        if (currentNumber.Length < maxDigits)
        {
            currentNumber += digit;
            UpdateDisplay();

            if (statusText != null)
                statusText.text = "";
        }
    }

    public void DeleteLastDigit()
    {
        if (loginEnProceso)
            return;

        if (currentNumber.Length > 0)
        {
            currentNumber = currentNumber.Substring(
                0,
                currentNumber.Length - 1
            );

            UpdateDisplay();

            if (statusText != null)
                statusText.text = "";
        }
    }

    private void UpdateDisplay()
    {
        if (phoneDisplay == null)
            return;

        phoneDisplay.text = string.IsNullOrEmpty(currentNumber)
            ? "Introduce tu teléfono..."
            : currentNumber;
    }

    public void SubmitPhoneNumber()
    {
        if (loginEnProceso)
            return;

        // Si no ha introducido ningún número
        if (string.IsNullOrWhiteSpace(currentNumber))
        {
            MostrarTelefonoIncorrecto();
            return;
        }

        if (statusText != null)
            statusText.text = "";

        loginEnProceso = true;

        StartCoroutine(CheckLogin(currentNumber));
    }

    IEnumerator CheckLogin(string phone)
    {
        WWWForm form = new WWWForm();
        form.AddField("telefono", phone);

        using (UnityWebRequest www = UnityWebRequest.Post(apiUrl, form))
        {
            yield return www.SendWebRequest();

            // Error real de conexión
            if (www.result != UnityWebRequest.Result.Success)
            {
                loginEnProceso = false;

                if (statusText != null)
                {
                    statusText.text = "Error de conexión con el servidor";
                    statusText.color = Color.red;
                }

                Debug.LogError(
                    "ERROR LOGIN VR\n" +
                    "Resultado: " + www.result + "\n" +
                    "HTTP: " + www.responseCode + "\n" +
                    "Error: " + www.error + "\n" +
                    "Respuesta PHP: " +
                    (www.downloadHandler != null
                        ? www.downloadHandler.text
                        : "Sin respuesta")
                );

                yield break;
            }

            string response = www.downloadHandler.text.Trim();

            Debug.Log("Respuesta login_vr.php: " + response);

            // El teléfono existe en la BBDD
            if (response.StartsWith("OK|"))
            {
                string[] partes = response.Split('|');

                if (partes.Length >= 3)
                {
                    int usuarioId;
                    int comentariosVR;

                    bool usuarioCorrecto =
                        int.TryParse(partes[1], out usuarioId);

                    bool comentariosCorrectos =
                        int.TryParse(partes[2], out comentariosVR);

                    if (usuarioCorrecto && comentariosCorrectos)
                    {
                        PlayerPrefs.SetInt(
                            "usuario_id",
                            usuarioId
                        );

                        PlayerPrefs.SetInt(
                            "comentarios_vr",
                            comentariosVR
                        );

                        PlayerPrefs.SetString(
                            "telefono_usuario",
                            phone
                        );

                        PlayerPrefs.Save();

                        Debug.Log(
                            "LOGIN CORRECTO | Usuario: " +
                            usuarioId +
                            " | Comentarios VR: " +
                            comentariosVR
                        );

                        if (statusText != null)
                            statusText.text = "";

                        AsyncOperation carga =
                            SceneManager.LoadSceneAsync(
                                "SampleScene"
                            );

                        if (carga != null)
                        {
                            while (!carga.isDone)
                                yield return null;
                        }
                        else
                        {
                            loginEnProceso = false;

                            if (statusText != null)
                            {
                                statusText.text =
                                    "Error al cargar la experiencia";

                                statusText.color = Color.red;
                            }
                        }

                        yield break;
                    }
                }

                // Si PHP devuelve OK pero los datos vienen mal
                loginEnProceso = false;

                if (statusText != null)
                {
                    statusText.text = "Error al iniciar sesión";
                    statusText.color = Color.red;
                }

                yield break;
            }

            // El teléfono NO existe en la BBDD
            MostrarTelefonoIncorrecto();
        }
    }

    private void MostrarTelefonoIncorrecto()
    {
        loginEnProceso = false;

        if (statusText != null)
        {
            statusText.text =
                "Teléfono incorrecto, introdúcelo de nuevo";

            statusText.color = Color.red;
        }

        currentNumber = "";
        UpdateDisplay();
    }
}