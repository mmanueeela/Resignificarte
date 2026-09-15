using UnityEngine;
using UnityEngine.UI;

public class RecordingButtonUI : MonoBehaviour
{
    [SerializeField] private Image botonImage;

    [SerializeField] private Sprite botonNormal;
    [SerializeField] private Sprite botonGrabando;
    [SerializeField] private Sprite botonGracias;

    public void MostrarGrabando()
    {
        botonImage.sprite = botonGrabando;
    }

    public void MostrarGracias()
    {
        botonImage.sprite = botonGracias;
    }

    public void MostrarNormal()
    {
        botonImage.sprite = botonNormal;
    }
}