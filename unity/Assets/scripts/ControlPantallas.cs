using UnityEngine;

public class ControlPantallas : MonoBehaviour
{
    [SerializeField] private GameObject[] pantallas;

    private int pantallaActual = 0;

    private void Start()
    {
        MostrarPantalla(0);
    }

    public void Siguiente()
    {
        if (pantallaActual < pantallas.Length - 1)
        {
            pantallaActual++;
            MostrarPantalla(pantallaActual);
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
