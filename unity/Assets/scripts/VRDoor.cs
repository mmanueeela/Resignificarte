using UnityEngine;
using UnityEngine.XR.Interaction.Toolkit;
using UnityEngine.XR.Interaction.Toolkit.Interactables;

public class VRDoor : MonoBehaviour
{
    [SerializeField] private Transform doorPivot;
    [SerializeField] private XRBaseInteractable handleInteractable;

    [Header("Límites de apertura")]
    [SerializeField] private float minAngle = 0f;
    [SerializeField] private float maxAngle = 100f;

    private Transform handTransform;
    private Quaternion closedRotation;

    private float startHandAngle;
    private float startDoorAngle;
    private float currentDoorAngle;

    private void Awake()
    {
        closedRotation = doorPivot.localRotation;
    }

    private void OnEnable()
    {
        Debug.Log("PUERTA: SCRIPT ACTIVO");

        handleInteractable.hoverEntered.AddListener(OnHoverEnter);
        handleInteractable.hoverExited.AddListener(OnHoverExit);

        handleInteractable.selectEntered.AddListener(OnGrab);
        handleInteractable.selectExited.AddListener(OnRelease);
    }

    private void OnDisable()
    {
        handleInteractable.hoverEntered.RemoveListener(OnHoverEnter);
        handleInteractable.hoverExited.RemoveListener(OnHoverExit);

        handleInteractable.selectEntered.RemoveListener(OnGrab);
        handleInteractable.selectExited.RemoveListener(OnRelease);
    }

    private void Update()
    {
        if (handTransform == null)
            return;

        float currentHandAngle = GetHandAngle();

        float difference = Mathf.DeltaAngle(startHandAngle, currentHandAngle);

        currentDoorAngle = Mathf.Clamp(
            startDoorAngle + difference,
            minAngle,
            maxAngle
        );

        doorPivot.localRotation =
            closedRotation *
            Quaternion.AngleAxis(currentDoorAngle, Vector3.up);
    }

    private void OnHoverEnter(HoverEnterEventArgs args)
    {
        Debug.Log("PUERTA: HOVER DETECTADO");
    }

    private void OnHoverExit(HoverExitEventArgs args)
    {
        Debug.Log("PUERTA: HOVER TERMINADO");
    }

    private void OnGrab(SelectEnterEventArgs args)
    {
        Debug.Log("PUERTA: GRAB DETECTADO");

        handTransform =
            args.interactorObject.GetAttachTransform(handleInteractable);

        startHandAngle = GetHandAngle();
        startDoorAngle = currentDoorAngle;
    }

    private void OnRelease(SelectExitEventArgs args)
    {
        Debug.Log("PUERTA: GRAB SOLTADO");

        handTransform = null;
    }

    private float GetHandAngle()
    {
        Transform reference = doorPivot.parent;

        Vector3 handLocal =
            reference.InverseTransformPoint(handTransform.position);

        Vector3 pivotLocal = doorPivot.localPosition;

        Vector3 direction = handLocal - pivotLocal;

        return Mathf.Atan2(direction.z, direction.x) * Mathf.Rad2Deg;
    }
}