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

    private int maxDigits = 10;
    private string currentNumber = "";

    void Start()
    {
        if (phoneDisplay == null)
            phoneDisplay = GameObject.Find("PhoneDisplay_Text").GetComponent<TextMeshProUGUI>();

        if (statusText == null)
            statusText = GameObject.Find("StatusDisplay_Text").GetComponent<TextMeshProUGUI>();

        statusText.text = "";
    }

    public void TypeDigit(string digit)
    {
        if (currentNumber.Length < maxDigits)
        {
            currentNumber += digit;
            UpdateDisplay();
        }
    }

    public void DeleteLastDigit()
    {
        if (currentNumber.Length > 0)
        {
            currentNumber = currentNumber.Substring(0, currentNumber.Length - 1);
            UpdateDisplay();
        }
    }

    private void UpdateDisplay()
    {
        phoneDisplay.text = string.IsNullOrEmpty(currentNumber)
            ? "Introduce tu teléfono..."
            : currentNumber;
    }

    public void SubmitPhoneNumber()
    {
        if (currentNumber.Length >= 9)
        {
            // No mostramos "Comprobando..."
            statusText.text = "";

            StartCoroutine(CheckLogin(currentNumber));
        }
    }

    IEnumerator CheckLogin(string phone)
    {
        WWWForm form = new WWWForm();
        form.AddField("telefono", phone);

        using (UnityWebRequest www = UnityWebRequest.Post(apiUrl, form))
        {
            yield return www.SendWebRequest();

            if (www.result != UnityWebRequest.Result.Success)
            {
                statusText.text = "Error de conexión con el servidor.";
                statusText.color = Color.yellow;
            }
            else
            {
                string response = www.downloadHandler.text.Trim();

                if (response.StartsWith("OK|"))
                {
                    // Guardamos el teléfono para usarlo después
                    PlayerPrefs.SetString("telefono_usuario", phone);
                    PlayerPrefs.Save();

                    // No mostramos ningún mensaje en pantalla
                    statusText.text = "";

                    // Esperamos 2 segundos
                    yield return new WaitForSeconds(2f);

                    // Cambiamos de escena
                    SceneManager.LoadScene("SampleScene");
                }
                else
                {
                    // Único mensaje visible para login incorrecto
                    statusText.text = "Teléfono incorrecto, introdúcelo de nuevo";
                    statusText.color = Color.red;

                    currentNumber = "";
                    UpdateDisplay();
                }
            }
        }
    }
}