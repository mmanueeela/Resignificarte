using UnityEngine;

#if UNITY_ANDROID && !UNITY_EDITOR
using UnityEngine.Android;
#endif

public class QuestMicrophoneManager : MonoBehaviour
{
#if UNITY_ANDROID && !UNITY_EDITOR
    private PermissionCallbacks permissionCallbacks;
#endif

    private void Start()
    {
#if UNITY_ANDROID && !UNITY_EDITOR

        if (Permission.HasUserAuthorizedPermission(Permission.Microphone))
        {
            Debug.Log("PERMISO MICROFONO: YA CONCEDIDO");
            return;
        }

        permissionCallbacks = new PermissionCallbacks();

        permissionCallbacks.PermissionGranted +=
            PermisoConcedido;

        permissionCallbacks.PermissionDenied +=
            PermisoDenegado;

        permissionCallbacks.PermissionDeniedAndDontAskAgain +=
            PermisoDenegadoPermanentemente;

        Permission.RequestUserPermission(
            Permission.Microphone,
            permissionCallbacks
        );

#endif
    }


#if UNITY_ANDROID && !UNITY_EDITOR

    private void PermisoConcedido(string permiso)
    {
        Debug.Log(
            "PERMISO MICROFONO CONCEDIDO: " +
            permiso
        );
    }


    private void PermisoDenegado(string permiso)
    {
        Debug.LogError(
            "PERMISO MICROFONO DENEGADO: " +
            permiso
        );
    }


    private void PermisoDenegadoPermanentemente(
        string permiso
    )
    {
        Debug.LogError(
            "PERMISO MICROFONO DENEGADO PERMANENTEMENTE: " +
            permiso
        );
    }

#endif


    public static bool MicrofonoSistemaMuteado()
    {
#if UNITY_ANDROID && !UNITY_EDITOR

        try
        {
            using AndroidJavaClass unityPlayer =
                new AndroidJavaClass(
                    "com.unity3d.player.UnityPlayer"
                );

            using AndroidJavaObject activity =
                unityPlayer.GetStatic<AndroidJavaObject>(
                    "currentActivity"
                );

            using AndroidJavaObject audioManager =
                activity.Call<AndroidJavaObject>(
                    "getSystemService",
                    "audio"
                );

            return audioManager.Call<bool>(
                "isMicrophoneMute"
            );
        }
        catch (System.Exception e)
        {
            Debug.LogWarning(
                "No se pudo comprobar mute del micro: " +
                e.Message
            );

            return false;
        }

#else

        return false;

#endif
    }
}