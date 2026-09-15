using UnityEngine;

public class AntonioController : MonoBehaviour
{
    [Header("Referencias")]
    [SerializeField] private Transform antonioRoot;
    [SerializeField] private Transform puntoCuartoEspecial;
    [SerializeField] private Animator animator;

    // Teletransporta a Antonio al cuarto especial
    public void TeletransportarAlCuartoEspecial()
    {
        if (antonioRoot == null || puntoCuartoEspecial == null || animator == null)
        {
            Debug.LogError("Falta alguna referencia en AntonioController.");
            return;
        }

        antonioRoot.position = puntoCuartoEspecial.position;
        antonioRoot.rotation = puntoCuartoEspecial.rotation;

        // Al llegar se queda de pie esperando
        animator.Play("Standing Idle", 0, 0f);

        Debug.Log("Antonio teletransportado al cuarto especial.");
    }

    // Empieza a gesticular cuando se reproduce el audio
    public void EmpezarGestos()
    {
        if (animator == null)
        {
            Debug.LogError("No hay Animator asignado.");
            return;
        }

        animator.SetTrigger("EmpezarGestos");

        Debug.Log("Antonio empieza a gesticular.");
    }

    // Para los gestos y vuelve a Standing Idle
    public void PararGestos()
    {
        if (animator == null)
        {
            Debug.LogError("No hay Animator asignado.");
            return;
        }

        animator.ResetTrigger("EmpezarGestos");

        // Vuelve directamente a la animación de espera
        animator.Play("Standing Idle", 0, 0f);

        Debug.Log("Antonio deja de gesticular.");
    }


    // -------- PRUEBAS --------

    [ContextMenu("Probar Teletransporte")]
    private void ProbarTeletransporte()
    {
        TeletransportarAlCuartoEspecial();
    }

    [ContextMenu("Probar Gestos")]
    private void ProbarGestos()
    {
        EmpezarGestos();
    }

    [ContextMenu("Probar Parar Gestos")]
    private void ProbarPararGestos()
    {
        PararGestos();
    }
}