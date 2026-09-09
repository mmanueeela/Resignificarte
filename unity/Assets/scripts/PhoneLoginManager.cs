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

                Debug.LogError(
                    "ERROR LOGIN VR\n" +
                    "Resultado: " + www.result + "\n" +
                    "HTTP: " + www.responseCode + "\n" +
                    "Error: " + www.error + "\n" +
                    "Respuesta PHP: " + www.downloadHandler.text
                );
            }
            else
            {
                string response = www.downloadHandler.text.Trim();

                if (response.StartsWith("OK|"))
                {
                    string[] partes = response.Split('|');

                    if (partes.Length >= 3)
                    {
                        int usuarioId;
                        int comentariosVR;

                        if (int.TryParse(partes[1], out usuarioId) &&
                            int.TryParse(partes[2], out comentariosVR))
                        {
                            PlayerPrefs.SetInt("usuario_id", usuarioId);
                            PlayerPrefs.SetInt("comentarios_vr", comentariosVR);
                            PlayerPrefs.SetString("telefono_usuario", phone);

                            PlayerPrefs.Save();

                            Debug.Log(
                                "Usuario: " + usuarioId +
                                " | Comentarios VR: " + comentariosVR
                            );

                            statusText.text = "";

                            yield return new WaitForSeconds(2f);

                            SceneManager.LoadScene("SampleScene");
                        }
                    }
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