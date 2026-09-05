using UnityEngine;

public class ControlAudio : MonoBehaviour
{
    [SerializeField] private AudioSource audioSource;

    [Header("Audios")]
    [SerializeField] private AudioClip obra1;
    [SerializeField] private AudioClip obra2;
    [SerializeField] private AudioClip obra3;
    [SerializeField] private AudioClip obra4;

    private bool estaPausado = false;
    private AudioClip audioActual;

    public void ReproducirObra1()
    {
        ReproducirAudio(obra1);
    }

    public void ReproducirObra2()
    {
        ReproducirAudio(obra2);
    }

    public void ReproducirObra3()
    {
        ReproducirAudio(obra3);
    }

    public void ReproducirObra4()
    {
        ReproducirAudio(obra4);
    }

    private void ReproducirAudio(AudioClip nuevoAudio)
    {
        // Si estaba pausada ESTA MISMA obra, continúa donde estaba
        if (estaPausado && audioActual == nuevoAudio)
        {
            audioSource.UnPause();
            estaPausado = false;
            return;
        }

        // Si es otra obra, empieza esa obra desde el principio
        audioActual = nuevoAudio;
        audioSource.clip = nuevoAudio;
        audioSource.Play();
        estaPausado = false;
    }

    public void Pausar()
    {
        if (audioSource.isPlaying)
        {
            audioSource.Pause();
            estaPausado = true;
        }
    }

    public void Detener()
    {
        audioSource.Stop();
        estaPausado = false;
        audioActual = null;
    }
}