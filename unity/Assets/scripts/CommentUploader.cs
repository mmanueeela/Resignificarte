using UnityEngine;
using UnityEngine.Networking;
using System.Collections;

public class CommentUploader : MonoBehaviour
{
    [Header("Servidor")]
    [SerializeField]
    private string apiUrl =
        "https://mzazzar.upv.edu.es/php/guardar_comentario.php";

    [Header("Progreso")]
    [SerializeField]
    private DoorProgressManager doorProgressManager;

    public void GuardarComentario(
        int obraId,
        string texto
    )
    {
        if (string.IsNullOrWhiteSpace(texto))
        {
            Debug.LogWarning(
                "No se puede guardar un comentario vacío."
            );

            return;
        }

        if (!PlayerPrefs.HasKey("usuario_id"))
        {
            Debug.LogError(
                "No existe usuario_id. " +
                "El usuario debe iniciar sesión primero."
            );

            return;
        }

        if (obraId < 1 || obraId > 4)
        {
            Debug.LogError(
                "obra_id no válido: " +
                obraId
            );

            return;
        }

        int usuarioId =
            PlayerPrefs.GetInt("usuario_id");

        StartCoroutine(
            EnviarComentario(
                usuarioId,
                obraId,
                texto
            )
        );
    }

    private IEnumerator EnviarComentario(
        int usuarioId,
        int obraId,
        string texto
    )
    {
        WWWForm form = new WWWForm();

        form.AddField(
            "usuario_id",
            usuarioId
        );

        form.AddField(
            "obra_id",
            obraId
        );

        form.AddField(
            "comentario",
            texto
        );

        using (
            UnityWebRequest www =
            UnityWebRequest.Post(
                apiUrl,
                form
            )
        )
        {
            yield return
                www.SendWebRequest();

            if (
                www.result !=
                UnityWebRequest.Result.Success
            )
            {
                Debug.LogError(
                    "Error enviando comentario: " +
                    www.error
                );

                yield break;
            }

            string response =
                www.downloadHandler
                    .text
                    .Trim();

            if (
                response.StartsWith("OK|")
            )
            {
                string[] partes =
                    response.Split('|');

                if (partes.Length >= 2)
                {
                    int comentariosVR;

                    if (
                        int.TryParse(
                            partes[1],
                            out comentariosVR
                        )
                    )
                    {
                        Debug.Log(
                            "Comentario guardado. " +
                            "Progreso VR: " +
                            comentariosVR +
                            "/3"
                        );

                        if (
                            doorProgressManager
                            != null
                        )
                        {
                            doorProgressManager
                                .ActualizarProgreso(
                                    comentariosVR
                                );
                        }
                    }
                }
            }
            else
            {
                Debug.LogError(
                    "Error del servidor: " +
                    response
                );
            }
        }
    }
}