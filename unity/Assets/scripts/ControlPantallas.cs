using UnityEngine;
using UnityEngine.SceneManagement;

public class ControlPantallas : MonoBehaviour
{
    [SerializeField] private GameObject[] pantallas;

    [Header("Escena final")]
    [SerializeField] private string siguienteEscena = "SampleScene";

    private int pantallaActual = 0;

    private void Start()
    {
        MostrarPantalla(0);
    }

    public void Siguiente()
    {
        // Si todavía no estamos en la última pantalla
        if (pantallaActual < pantallas.Length - 1)
        {
            pantallaActual++;
            MostrarPantalla(pantallaActual);
        }
        else
        {
            // Estamos en la Pantalla 7
            SceneManager.LoadScene(siguienteEscena);
        }
    }

    private void MostrarPantalla(int indice)
    {
        for (int i = 0; i < pantallas.Length; i++)
        {
            pantallas[i].SetActive(i == indice);
        }
    }
}