using UnityEngine;
using UnityEngine.InputSystem; // Añadir esta línea arriba
using TMPro;

public class ControlCandados : MonoBehaviour
{
    [Header("Candados de la Jerarquía")]
    [SerializeField] private GameObject candadoCuarto2;
    [SerializeField] private GameObject candadoCuarto3;
    [SerializeField] private GameObject candadoCuartoEspecial;

    [Header("UI Mensaje de Pantalla")]
    [SerializeField] private TextMeshProUGUI textoMensaje;

    private int candadosDesbloqueados = 0;

    private void Start()
    {
        if (candadoCuarto2 != null) candadoCuarto2.SetActive(true);
        if (candadoCuarto3 != null) candadoCuarto3.SetActive(true);
        if (candadoCuartoEspecial != null) candadoCuartoEspecial.SetActive(true);

        if (textoMensaje != null) 
            textoMensaje.text = "Pulsa ESPACIO para desbloquear el candado.";
    }

    private void Update()
    {
        // Detección compatible con el nuevo Input System y el antiguo
        bool espacioPulsado = false;

        if (Keyboard.current != null && Keyboard.current.spaceKey.wasPressedThisFrame)
        {
            espacioPulsado = true;
        }

        if (espacioPulsado)
        {
            DesbloquearSiguienteCuarto();
        }
    }

    public void DesbloquearSiguienteCuarto()
    {
        if (candadosDesbloqueados == 0)
        {
            if (candadoCuarto2 != null) candadoCuarto2.SetActive(false);
            if (textoMensaje != null) textoMensaje.text = "¡Desbloqueaste el siguiente cuarto!";
            candadosDesbloqueados = 1;
        }
        else if (candadosDesbloqueados == 1)
        {
            if (candadoCuarto3 != null) candadoCuarto3.SetActive(false);
            if (textoMensaje != null) textoMensaje.text = "¡Desbloqueaste el siguiente cuarto!";
            candadosDesbloqueados = 2;
        }
        else if (candadosDesbloqueados == 2)
        {
            if (candadoCuartoEspecial != null) candadoCuartoEspecial.SetActive(false);
            if (textoMensaje != null) textoMensaje.text = "¡Desbloqueaste el cuarto especial!";
            candadosDesbloqueados = 3;
        }
    }
}