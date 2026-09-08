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

    [Header("Sensación")]
    [SerializeField] private float degreesPerMeter = 200f;
    [SerializeField] private float smoothSpeed = 14f;
    [SerializeField] private float deadZoneDegrees = 0.3f;

    [Header("Dirección")]
    [SerializeField] private bool invertDirection = false;

    private Transform handTransform;

    private Quaternion closedRotation;

    private Vector3 grabHandPosition;
    private Vector3 movementAxis;

    private float grabDoorAngle;
    private float currentDoorAngle;
    private float targetDoorAngle;

    private void Awake()
    {
        closedRotation = doorPivot.localRotation;

        CalculateMovementAxis();
    }

    private void OnEnable()
    {
        handleInteractable.selectEntered.AddListener(OnGrab);
        handleInteractable.selectExited.AddListener(OnRelease);
    }

    private void OnDisable()
    {
        handleInteractable.selectEntered.RemoveListener(OnGrab);
        handleInteractable.selectExited.RemoveListener(OnRelease);
    }

    private void Update()
    {
        if (handTransform != null)
        {
            Vector3 handMovement =
                handTransform.position - grabHandPosition;

            float movement =
                Vector3.Dot(handMovement, movementAxis);

            if (invertDirection)
                movement *= -1f;

            float desiredAngle =
                grabDoorAngle + movement * degreesPerMeter;

            desiredAngle = Mathf.Clamp(
                desiredAngle,
                minAngle,
                maxAngle
            );

            if (Mathf.Abs(desiredAngle - targetDoorAngle) > deadZoneDegrees)
            {
                targetDoorAngle = desiredAngle;
            }
        }

        currentDoorAngle = Mathf.Lerp(
            currentDoorAngle,
            targetDoorAngle,
            1f - Mathf.Exp(-smoothSpeed * Time.deltaTime)
        );

        doorPivot.localRotation =
            closedRotation *
            Quaternion.AngleAxis(currentDoorAngle, Vector3.up);
    }

    private void OnGrab(SelectEnterEventArgs args)
    {
        handTransform =
            args.interactorObject.GetAttachTransform(handleInteractable);

        grabHandPosition = handTransform.position;
        grabDoorAngle = currentDoorAngle;
        targetDoorAngle = currentDoorAngle;
    }

    private void OnRelease(SelectExitEventArgs args)
    {
        handTransform = null;
    }

    private void CalculateMovementAxis()
    {
        Vector3 hingeAxis =
            doorPivot.TransformDirection(Vector3.up);

        Vector3 handleDirection =
            handleInteractable.transform.position - doorPivot.position;

        handleDirection =
            Vector3.ProjectOnPlane(handleDirection, hingeAxis);

        if (handleDirection.sqrMagnitude < 0.0001f)
        {
            movementAxis = doorPivot.parent.right;
            return;
        }

        handleDirection.Normalize();

        movementAxis =
            Vector3.Cross(hingeAxis, handleDirection).normalized;
    }
}