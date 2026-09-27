using UnityEngine;
using UnityEngine.SceneManagement;

public class ExitDoorPrompt : MonoBehaviour
{
    [Header("Puerta")]
    public Transform doorPivot;

    [Header("Panel de confirmación")]
    public GameObject exitPanel;

    [Header("A partir de qué ángulo aparece")]
    public float activationAngle = 60f;

    private bool panelShown = false;

    void Start()
    {
        if (exitPanel != null)
            exitPanel.SetActive(false);
    }

    void Update()
    {
        if (doorPivot == null || exitPanel == null)
            return;

        float angle = doorPivot.localEulerAngles.y;

        if (angle > 180f)
            angle -= 360f;

        if (Mathf.Abs(angle) >= activationAngle && !panelShown)
        {
            exitPanel.SetActive(true);
            panelShown = true;
        }
    }

    public void YesExit()
    {
        Debug.Log("El usuario quiere salir");

        // Si quieres volver a una escena de inicio:
        SceneManager.LoadScene("SampleScene");

        // Si más adelante quieres cerrar la app:
        // Application.Quit();
    }

    public void NoExit()
    {
        exitPanel.SetActive(false);

        // Para que pueda volver a aparecer si vuelve a abrir la puerta
        panelShown = false;
    }
}