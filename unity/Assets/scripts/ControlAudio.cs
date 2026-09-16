using UnityEngine;

public class ControlAudio : MonoBehaviour
{
    [SerializeField] private AudioSource audioSource;

    [Header("Audios")]
    [SerializeField] private AudioClip obra1;
    [SerializeField] private AudioClip obra2;
    [SerializeField] private AudioClip obra3;
    [SerializeField] private AudioClip obra4;

    [Header("Grabación de cada obra")]
    [SerializeField] private SpeechRecognitionTest grabacionObra1;
    [SerializeField] private SpeechRecognitionTest grabacionObra2;
    [SerializeField] private SpeechRecognitionTest grabacionObra3;
    [SerializeField] private SpeechRecognitionTest grabacionObra4;

    private bool estaPausado = false;
    private AudioClip audioActual;
    private int obraActual = 0;

    // Sirve para saber si estamos esperando
    // a que el audio termine de forma natural.
    private bool esperandoFinAudio = false;

    // Última posición conocida del audio.
    private int ultimoTimeSamples = 0;

    private void Awake()
    {
        if (audioSource != null)
        {
            // Muy importante:
            // los audios de las obras no deben reproducirse en bucle.
            audioSource.loop = false;
        }
    }

    private void Update()
    {
        if (audioSource == null || audioActual == null || !esperandoFinAudio)
            return;

        // Mientras está reproduciéndose, guardamos hasta dónde ha llegado.
        if (audioSource.isPlaying)
        {
            ultimoTimeSamples = audioSource.timeSamples;
            return;
        }

        // Si simplemente está pausado, NO significa que haya terminado.
        if (estaPausado)
            return;

        // Comprobamos que realmente haya llegado prácticamente hasta el final.
        int margenFinal = Mathf.RoundToInt(audioActual.frequency * 0.25f);
        bool llegoAlFinal = ultimoTimeSamples >= audioActual.samples - margenFinal;

        if (llegoAlFinal)
        {
            esperandoFinAudio = false;
            HabilitarGrabacionObra(obraActual);
        }
    }

    // =====================================================
    // OBRA 1
    // =====================================================
    public void ReproducirObra1()
    {
        ReproducirAudio(obra1, 1);
    }

    // =====================================================
    // OBRA 2
    // =====================================================
    public void ReproducirObra2()
    {
        ReproducirAudio(obra2, 2);
    }

    // =====================================================
    // OBRA 3
    // =====================================================
    public void ReproducirObra3()
    {
        ReproducirAudio(obra3, 3);
    }

    // =====================================================
    // OBRA 4
    // =====================================================
    public void ReproducirObra4()
    {
        ReproducirAudio(obra4, 4);
    }

    // =====================================================
    // REPRODUCCIÓN
    // =====================================================
    private void ReproducirAudio(AudioClip nuevoAudio, int numeroObra)
    {
        if (audioSource == null || nuevoAudio == null)
            return;

        // Si está pausada ESTA MISMA obra, continúa exactamente donde estaba.
        if (estaPausado && audioActual == nuevoAudio)
        {
            audioSource.UnPause();
            estaPausado = false;
            return;
        }

        // Si se reproduce otra obra, empieza desde el principio.
        audioActual = nuevoAudio;
        obraActual = numeroObra;
        ultimoTimeSamples = 0;

        audioSource.Stop();
        audioSource.clip = nuevoAudio;
        audioSource.time = 0f;
        audioSource.Play();

        estaPausado = false;
        esperandoFinAudio = true;
    }

    // =====================================================
    // PAUSA
    // =====================================================
    public void Pausar()
    {
        if (audioSource != null && audioSource.isPlaying)
        {
            audioSource.Pause();
            estaPausado = true;
        }
    }

    // =====================================================
    // DETENER
    // =====================================================
    public void Detener()
    {
        if (audioSource != null)
        {
            audioSource.Stop();
        }

        estaPausado = false;

        // Si el usuario lo detiene manualmente,
        // NO cuenta como haber escuchado el audio.
        esperandoFinAudio = false;
        ultimoTimeSamples = 0;
        audioActual = null;
        obraActual = 0;
    }

    // =====================================================
    // AUDIO TERMINADO
    // =====================================================
    private void HabilitarGrabacionObra(int numeroObra)
    {
        switch (numeroObra)
        {
            case 1:
                if (grabacionObra1 != null)
                {
                    grabacionObra1.HabilitarGrabacionTrasAudio();
                }
                break;

            case 2:
                if (grabacionObra2 != null)
                {
                    grabacionObra2.HabilitarGrabacionTrasAudio();
                }
                break;

            case 3:
                if (grabacionObra3 != null)
                {
                    grabacionObra3.HabilitarGrabacionTrasAudio();
                }
                break;

            case 4:
                if (grabacionObra4 != null)
                {
                    grabacionObra4.HabilitarGrabacionTrasAudio();
                }
                break;
        }
    }
}