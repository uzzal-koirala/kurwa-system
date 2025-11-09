 const inputs = document.querySelectorAll(".otp-input");

    // Auto move cursor between boxes
    inputs.forEach((input, index) => {
      input.addEventListener("input", (e) => {
        const value = e.target.value;
        if (value.length === 1 && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
      });
      input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && input.value === "" && index > 0) {
          inputs[index - 1].focus();
        }
      });
    });

    // Paste full OTP at once
    inputs[0].addEventListener("paste", (e) => {
      e.preventDefault();
      const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim();
      if (pasteData.length === inputs.length) {
        pasteData.split("").forEach((char, i) => {
          if (inputs[i]) inputs[i].value = char;
        });
      }
    });