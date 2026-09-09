using UnityEngine;

public class DoorProgressManager : MonoBehaviour
{
    [Header("Puertas bloqueadas")]
    [SerializeField] private GameObject doorHandlePuerta2;
    [SerializeField] private GameObject doorHandlePuerta3;
    [SerializeField] private GameObject doorHandlePuertaFinal;

    private int comentariosVR;

    private void Start()
    {
        comentariosVR = PlayerPrefs.GetInt("comentarios_vr", 0);
        ActualizarPuertas();
    }

    public void ActualizarProgreso(int nuevoProgreso)
    {
        comentariosVR = nuevoProgreso;

        PlayerPrefs.SetInt("comentarios_vr", comentariosVR);
        PlayerPrefs.Save();

        ActualizarPuertas();
    }

    private void ActualizarPuertas()
    {
        // Con 1 comentario se desbloquea la puerta 2
        if (doorHandlePuerta2 != null)
        {
            doorHandlePuerta2.SetActive(comentariosVR >= 1);
        }

        // Con 2 comentarios se desbloquea la puerta 3
        if (doorHandlePuerta3 != null)
        {
            doorHandlePuerta3.SetActive(comentariosVR >= 2);
        }

        // Con 3 comentarios se desbloquea la puerta final
        if (doorHandlePuertaFinal != null)
        {
            doorHandlePuertaFinal.SetActive(comentariosVR >= 3);
        }

        Debug.Log("Comentarios VR: " + comentariosVR);
    }
}