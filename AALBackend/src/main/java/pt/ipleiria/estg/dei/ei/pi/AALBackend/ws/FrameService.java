package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;


import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.*;

import org.apache.commons.io.FileUtils;
import org.apache.commons.io.IOUtils;
import org.jboss.resteasy.plugins.providers.multipart.InputPart;
import org.jboss.resteasy.plugins.providers.multipart.MultipartFormDataInput;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.EmotionDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.*;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.ClientDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.FrameDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.IterationDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.FrameBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.IterationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Frame;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.*;
import java.util.stream.Collectors;

@Path("frames")
@RolesAllowed({"Client", "Administrator"})
public class FrameService {
    @EJB
    private FrameBean frameBean;
    @EJB
    private IterationBean iterationBean;
    @EJB
    private PersonBean personBean;

    @Context
    private SecurityContext securityContext;

    @POST
    @Path("upload")
    @RolesAllowed({"Client"})
    @Consumes(MediaType.MULTIPART_FORM_DATA)
    @Produces(MediaType.APPLICATION_JSON)
    public IterationDTO upload(MultipartFormDataInput input, @HeaderParam("Authorization") String auth) throws Exception {
        Map<String, List<InputPart>> uploadForm = input.getFormDataMap();
        // Get file data to save
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        String macAddress = uploadForm.get("macAddress").get(0).getBodyAsString();
        String emotion = uploadForm.get("emotion").get(0).getBodyAsString();
        Iteration iteration = iterationBean.findIteration(iterationBean.create(macAddress, emotion, clientEmail));
        if(iteration == null)
            throw new MyEntityNotFoundException("Iteration could not be created.");
        List<InputPart> datesFrames = uploadForm.get("datesFrames");
        List<Date> dates = new LinkedList<>();
        for (InputPart inputPart : datesFrames) {
            Date date = new SimpleDateFormat("YYYY-MM-dd kk:mm:ss").parse(inputPart.getBodyAsString());
            dates.add(date);
        }
        List<InputPart> inputParts = uploadForm.get("file");
        int index = 0;
        for (InputPart inputPart : inputParts) {
            try {
                MultivaluedMap<String, String> header = inputPart.getHeaders(); 

                String filename = getFilename(header);
                // convert the uploaded file to inputstream
                InputStream inputStream = inputPart.getBody(InputStream.class, null);
                byte[] bytes = IOUtils.toByteArray(inputStream);
                String path = System.getProperty("user.home") + File.separator + "uploads" + File.separator + iteration.getId(); 
                File customDir = new File(path);
                if (!customDir.exists()) {
                    customDir.mkdir();
                }

                String filepath =  customDir.getCanonicalPath() + File.separator + filename;
                
                writeFile(bytes, filepath);
                if (!customDir.exists())
                    continue;
                frameBean.create(filename, filepath, iteration.getId(), dates.get(index));
            } catch (Exception e) {
                e.printStackTrace();
            }
            index++;
        }
        System.out.println("FrameService - Upload"+iteration.getFrames().size());
        return iterationToDTO(iterationBean.findIteration(iteration.getId()));
    }

    @GET
    @Path("download/{id}")
    @Produces(MediaType.TEXT_PLAIN)
    public Response download(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Frame frame = frameBean.findFrame(id);

        if (!securityContext.isUserInRole("Administrator") && frame.getIteration().getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to see this frame");

        File fileDownload = new File(frame.getPath());
        byte[] fileContent = FileUtils.readFileToByteArray(fileDownload);
        String encodedString = Base64.getEncoder().encodeToString(fileContent);
        Response.ResponseBuilder response = Response.ok(encodedString);
        response.header("Content-Disposition", "attachment;filename=" + frame.getName());
        return response.build();
    }

    @DELETE
    @Path("delete/{id}")
    @Produces(MediaType.APPLICATION_OCTET_STREAM)
    @RolesAllowed({"Administrator"})
    public Response delete(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Frame frame = frameBean.findFrame(id);

        File fileToDelete = new File(frame.getPath() + File.separator + frame.getName());
        if (fileToDelete.delete()) {
            if (!frameBean.delete(frame))
                return Response.status(400,"File deleted, with errors").build();

            return Response.ok("File deleted").build();
        }

        return Response.status(400, "File was not deleted").build();
    }

    @GET
    @Path("/iteration/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    public List<FrameDTO> getFramesByIteration(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Iteration iteration = iterationBean.findIteration(id);
        
        if (!securityContext.isUserInRole("Administrator") && iteration.getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to view this frame");

        return extendedFramesToDTOs(frameBean.getIterationFrames(iteration));
    }


    @PATCH
    @Path("{id}/classify")
    @Produces(MediaType.APPLICATION_JSON)
    public Response classifyFrame(@PathParam("id") long id, @HeaderParam("Authorization") String auth, EmotionDTO emotionDTO) throws Exception {
        Frame frame = frameBean.findFrame(id);

        if (!securityContext.isUserInRole("Administrator") && frame.getIteration().getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to view this iteration");
        frame = frameBean.classify(frame.getId(), emotionDTO.getName());

        return Response.status(Response.Status.OK).entity(extendedToDTO(frame)).build();
    }

    @GET
    @Path("{id}/exists")
    public Response hasFrames(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Iteration iteration = iterationBean.findIteration(id);
        
        if (!securityContext.isUserInRole("Administrator") && iteration.getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to view this frame");

        return Response.status(Response.Status.OK).entity(!iteration.getFrames().isEmpty()).build();
    }

    FrameDTO toDTO(Frame frame) { return new FrameDTO(
            frame.getId(),
            frame.getName(),
            frame.getPath(),
            frame.getCreateDate());
    }

    FrameDTO extendedToDTO(Frame frame) {
        return new FrameDTO(
                frame.getId(),
                frame.getName(),
                frame.getPath(),
                emotionToDTO(frame.getEmotion() == null ? new Emotion() : frame.getEmotion())
        );
    }

    EmotionDTO emotionToDTO(Emotion emotion) {
        return new EmotionDTO(
                emotion.getName(),
                emotion.getGroup()
        );
    }

    List<FrameDTO> framesToDTOs(List<Frame> frames) {
        return frames.stream().map(this::toDTO).collect(Collectors.toList());
    }
    List<FrameDTO> extendedFramesToDTOs(List<Frame> frames) {
        return frames.stream().map(this::extendedToDTO).collect(Collectors.toList());
    }

    private IterationDTO iterationToDTO(Iteration iteration) {
        return new IterationDTO(
            iteration.getId(),
            iteration.getMacAddress(),
            iteration.getEmotion(),
            iteration.getCreated_at(),
            clientToDTO(iteration.getClient()),
            framesToDTOs(iteration.getFrames())
        );
    }


    private ClientDTO clientToDTO(Client client) {
        return new ClientDTO(
            client.getEmail(),
            client.getName(),
            client.getBirthDate(),
            client.getContact()
        );
    }

    private String getFilename(MultivaluedMap<String, String> header) {
        String[] contentDisposition = header.getFirst("Content-Disposition").split(";"); for (String filename : contentDisposition) {
            if ((filename.trim().startsWith("filename"))) {
                String[] name = filename.split("=");
                String finalFileName = name[1].trim().replaceAll("\"", ""); return finalFileName;
            } }
        return "unknown";
    }

    private void writeFile(byte[] content, String filename) throws IOException {
        File file = new File(filename);
        if (!file.exists()) {
            file.createNewFile();
        }
        FileOutputStream fop = new FileOutputStream(file);
        fop.write(content);
        fop.flush();
        fop.close();
    }
}
