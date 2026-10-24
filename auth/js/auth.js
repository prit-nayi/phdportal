async function handleCredentialResponse(googleResponse) {
  const googleToken = googleResponse.credential;

  const response = await fetch(url("/auth/api/login"), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      googleToken,
    }),
  });

  if (!response.ok) {
    let errorMessage = "";
    try {
      const json = await response.json();
      errorMessage = createErrorMessage(`Could not login: ${json.message}`);
    } catch {
      errorMessage = createErrorMessage("Could not login, please try again.");
    }

    document.getElementById("message").innerHTML = errorMessage;
    return;
  }

  window.location.href = url("/");
}
