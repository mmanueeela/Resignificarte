using UnityEngine;
using UnityEngine.Networking;
using System.Collections;

public class CommentUploader : MonoBehaviour
{
    [Header("Servidor")]
    [SerializeField] private string apiUrl = "https://mzazzar.upv.edu.es/php/guardar_comentario.php";

    [Header("Progreso")]
    [SerializeField] private DoorProgressManager doorProgressManager;
    [SerializeField] private RoomProgressUIManager roomProgressUIManager;

    public void GuardarComentario(int obraId, string texto)
    {
        if (string.IsNullOrWhiteSpace(texto))
            return;

        if (!PlayerPrefs.HasKey("usuario_id"))
            return;

        if (obraId < 1 || obraId > 4)
            return;

        int usuarioId = PlayerPrefs.GetInt("usuario_id");

        StartCoroutine(EnviarComentario(usuarioId, obraId, texto));
    }

    private IEnumerator EnviarComentario(int usuarioId, int obraId, string texto)
    {
        WWWForm form = new WWWForm();
        form.AddField("usuario_id", usuarioId);
        form.AddField("obra_id", obraId);
        form.AddField("comentario", texto);

        using (UnityWebRequest www = UnityWebRequest.Post(apiUrl, form))
        {
            yield return www.SendWebRequest();

            if (www.result != UnityWebRequest.Result.Success)
                yield break;

            string response = www.downloadHandler.text.Trim();

            if (!response.StartsWith("OK|"))
                yield break;

            string[] partes = response.Split('|');

            if (partes.Length < 3)
                yield break;

            if (!int.TryParse(partes[1], out int comentariosVR))
                yield break;

            if (!int.TryParse(partes[2], out int finalInt))
                yield break;

            bool finalComentada = finalInt == 1;

            // Guardar progreso
            PlayerPrefs.SetInt("comentarios_vr", comentariosVR);
            PlayerPrefs.SetInt("obra_final_comentada", finalComentada ? 1 : 0);
            PlayerPrefs.Save();

            // Actualizar puertas
            if (doorProgressManager != null)
            {
                doorProgressManager.ActualizarProgreso(comentariosVR);
            }

            // Actualizar botones y mensajes
            if (roomProgressUIManager != null)
            {
                roomProgressUIManager.ActualizarEstado(comentariosVR, finalComentada);
            }
        }
    }
}