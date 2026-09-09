using UnityEngine;

[CreateAssetMenu(
    fileName = "HFSecrets",
    menuName = "Config/HF Secrets"
)]
public class HFSecrets : ScriptableObject
{
    public string huggingFaceToken;
}