using UnityEngine;

public class AntonioController : MonoBehaviour
{
    [Header("Referencias")]
    [SerializeField] private Transform antonioRoot;
    [SerializeField] private Transform puntoCuartoEspecial;
    [SerializeField] private Animator animator;

    // Teletransporta a Antonio al cuarto especial
    // y lo pone directamente en Standing Idle.
    public void TeletransportarAlCuartoEspecial()
    {
        if (antonioRoot == null || puntoCuartoEspecial == null || animator == null)
        {
            Debug.LogError("Falta alguna referencia en AntonioController.");
            return;
        }

        antonioRoot.position = puntoCuartoEspecial.position;
        antonioRoot.rotation = puntoCuartoEspecial.rotation;

        animator.Play("Standing Idle", 0, 0f);

        Debug.Log("Antonio teletransportado al cuarto especial.");
    }

    // Se llamará cuando el usuario pulse Play/Escuchar audio.
    public void EmpezarGestos()
    {
        if (animator == null)
        {
            Debug.LogError("No hay Animator asignado en AntonioController.");
            return;
        }

        animator.SetTrigger("EmpezarGestos");

        Debug.Log("Antonio empieza a gesticular.");
    }

    // SOLO PARA HACER PRUEBAS DESDE EL INSPECTOR.
    [ContextMenu("Probar Teletransporte")]
    private void ProbarTeletransporte()
    {
        TeletransportarAlCuartoEspecial();
    }

    // SOLO PARA HACER PRUEBAS DESDE EL INSPECTOR.
    [ContextMenu("Probar Gestos")]
    private void ProbarGestos()
    {
        EmpezarGestos();
    }
}