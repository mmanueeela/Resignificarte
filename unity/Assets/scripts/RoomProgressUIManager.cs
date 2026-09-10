using TMPro;
using UnityEngine;

public class RoomProgressUIManager : MonoBehaviour
{
    [Header("Controles de grabación")]
    [SerializeField] private GameObject grabacionSala1;
    [SerializeField] private GameObject grabacionSala2;
    [SerializeField] private GameObject grabacionSala3;
    [SerializeField] private GameObject grabacionSala4;

    [Header("Mensajes")]
    [SerializeField] private TMP_Text mensajeSala1;
    [SerializeField] private TMP_Text mensajeSala2;
    [SerializeField] private TMP_Text mensajeSala3;
    [SerializeField] private TMP_Text mensajeSala4;

    private void Awake()
    {
        int comentariosVR = PlayerPrefs.GetInt("comentarios_vr", 0);
        bool finalComentada = PlayerPrefs.GetInt("obra_final_comentada", 0) == 1;

        ActualizarEstado(comentariosVR, finalComentada);
    }

    public void ActualizarEstado(int comentariosVR, bool finalComentada)
    {
        // ==========================================
        // APAGAR TODAS LAS GRABACIONES
        // ==========================================
        if (grabacionSala1 != null)
            grabacionSala1.SetActive(false);

        if (grabacionSala2 != null)
            grabacionSala2.SetActive(false);

        if (grabacionSala3 != null)
            grabacionSala3.SetActive(false);

        if (grabacionSala4 != null)
            grabacionSala4.SetActive(false);

        // ==========================================
        // OCULTAR TODOS LOS MENSAJES
        // ==========================================
        if (mensajeSala1 != null)
            mensajeSala1.gameObject.SetActive(false);

        if (mensajeSala2 != null)
            mensajeSala2.gameObject.SetActive(false);

        if (mensajeSala3 != null)
            mensajeSala3.gameObject.SetActive(false);

        if (mensajeSala4 != null)
            mensajeSala4.gameObject.SetActive(false);

        // ==========================================
        // SALA 1
        // ==========================================
        if (comentariosVR == 0)
        {
            if (grabacionSala1 != null)
                grabacionSala1.SetActive(true);

            return;
        }

        if (mensajeSala1 != null)
        {
            mensajeSala1.text = "Ya puedes acceder a la sala 2";
            mensajeSala1.gameObject.SetActive(true);
        }

        // ==========================================
        // SALA 2
        // ==========================================
        if (comentariosVR == 1)
        {
            if (grabacionSala2 != null)
                grabacionSala2.SetActive(true);

            return;
        }

        if (mensajeSala2 != null)
        {
            mensajeSala2.text = "Ya puedes acceder a la sala 3";
            mensajeSala2.gameObject.SetActive(true);
        }

        // ==========================================
        // SALA 3
        // ==========================================
        if (comentariosVR == 2)
        {
            if (grabacionSala3 != null)
                grabacionSala3.SetActive(true);

            return;
        }

        if (mensajeSala3 != null)
        {
            mensajeSala3.text = "Ya puedes acceder a la sala final";
            mensajeSala3.gameObject.SetActive(true);
        }

        // ==========================================
        // SALA FINAL
        // ==========================================
        if (!finalComentada)
        {
            if (grabacionSala4 != null)
                grabacionSala4.SetActive(true);

            return;
        }

        if (mensajeSala4 != null)
        {
            mensajeSala4.text = "Muchas gracias por comentar todos los cuadros. Has completado la experiencia.";
            mensajeSala4.gameObject.SetActive(true);
        }
    }
}