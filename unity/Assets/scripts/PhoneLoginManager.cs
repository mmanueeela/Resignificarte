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

    private const int maxDigits = 15;

    private string currentNumber = "";
    private bool loginEnProceso = false;

    private void Start()
    {
        if (phoneDisplay == null)
        {
            GameObject phoneObject = GameObject.Find("PhoneDisplay_Text");
            if (phoneObject != null)
            {
                phoneDisplay = phoneObject.GetComponent<TextMeshProUGUI>();
            }
        }

        if (statusText == null)
        {
            GameObject statusObject = GameObject.Find("StatusDisplay_Text");
            if (statusObject != null)
            {
                statusText = statusObject.GetComponent<TextMeshProUGUI>();
            }
        }

        if (statusText != null)
            statusText.text = "";

        UpdateDisplay();
    }

    public void TypeDigit(string digit)
    {
        if (loginEnProceso)
            return;

        if (currentNumber.Length >= maxDigits)
            return;

        currentNumber += digit;
        UpdateDisplay();

        if (statusText != null)
            statusText.text = "";
    }

    public void DeleteLastDigit()
    {
        if (loginEnProceso)
            return;

        if (currentNumber.Length == 0)
            return;

        currentNumber = currentNumber.Substring(0, currentNumber.Length - 1);
        UpdateDisplay();

        if (statusText != null)
            statusText.text = "";
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

    private IEnumerator CheckLogin(string phone)
    {
        WWWForm form = new WWWForm();
        form.AddField("telefono", phone);

        using (UnityWebRequest www = UnityWebRequest.Post(apiUrl, form))
        {
            yield return www.SendWebRequest();

            if (www.result != UnityWebRequest.Result.Success)
            {
                loginEnProceso = false;

                if (statusText != null)
                {
                    statusText.text = "Error de conexión con el servidor";
                    statusText.color = Color.red;
                }

                yield break;
            }

            string response = www.downloadHandler.text.Trim();

            if (!response.StartsWith("OK|"))
            {
                MostrarTelefonoIncorrecto();
                yield break;
            }

            string[] partes = response.Split('|');

            if (partes.Length < 3)
            {
                MostrarErrorLogin();
                yield break;
            }

            if (!int.TryParse(partes[1], out int usuarioId) || !int.TryParse(partes[2], out int comentariosVR))
            {
                MostrarErrorLogin();
                yield break;
            }

            PlayerPrefs.SetInt("usuario_id", usuarioId);
            PlayerPrefs.SetInt("comentarios_vr", comentariosVR);
            PlayerPrefs.SetString("telefono_usuario", phone);
            PlayerPrefs.Save();

            if (statusText != null)
                statusText.text = "";

            AsyncOperation carga = SceneManager.LoadSceneAsync("SampleScene");

            if (carga == null)
            {
                loginEnProceso = false;

                if (statusText != null)
                {
                    statusText.text = "Error al cargar la experiencia";
                    statusText.color = Color.red;
                }

                yield break;
            }

            while (!carga.isDone)
                yield return null;
        }
    }

    private void MostrarTelefonoIncorrecto()
    {
        loginEnProceso = false;

        if (statusText != null)
        {
            statusText.text = "Teléfono incorrecto, introdúcelo de nuevo";
            statusText.color = Color.red;
        }

        currentNumber = "";
        UpdateDisplay();
    }

    private void MostrarErrorLogin()
    {
        loginEnProceso = false;

        if (statusText != null)
        {
            statusText.text = "Error al iniciar sesión";
            statusText.color = Color.red;
        }
    }
}